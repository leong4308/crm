<?php

namespace Aether\Email\Helpers;

class HtmlFilter
{
    /**
     * Impresión de etiquetas.
     *
     * @param  string  $tagname
     * @param  array  $attary
     * @param  int  $tagtype
     * @return string
     */
    public function tln_tagprint($tagname, $attary, $tagtype)
    {
        if ($tagtype == 2) {
            $fulltag = '</'.$tagname.'>';
        } else {
            $fulltag = '<'.$tagname;

            if (is_array($attary) && count($attary)) {
                $atts = [];

                foreach ($attary as $attname => $attvalue) {
                    array_push($atts, "$attname=$attvalue");
                }

                $fulltag .= ' '.implode(' ', $atts);
            }

            if ($tagtype == 3) {
                $fulltag .= ' /';
            }

            $fulltag .= '>';
        }

        return $fulltag;
    }

    /**
     * Una pequeña función auxiliar para usar con array_walk. Modifica un by-ref
     * valor y lo pone en minúsculas.
     *
     * @param  string  $val
     * @return void
     */
    public function tln_casenormalize(&$val)
    {
        $val = strtolower($val);
    }

    /**
     * Esta función omite cualquier espacio en blanco desde la posición actual dentro
     * una cadena y al siguiente valor que no sea un espacio en blanco.
     *
     * @param  string  $body
     * @param  int  $offset
     * @return int
     */
    public function tln_skipspace($body, $offset)
    {
        preg_match('/^(\s*)/s', substr($body, $offset), $matches);

        try {
            if (! empty($matches[1])) {
                $count = strlen($matches[1]);
                $offset += $count;
            }
        } catch (\Exception $e) {
        }

        return $offset;
    }

    /**
     * Esta función busca el siguiente carácter dentro de una cadena.  Es
     * en realidad sólo un "strpos" glorificado, excepto que capta los fracasos
     * bien.
     *
     * @param  string  $body
     * @param  int  $offset
     * @param  string  $needle
     * @return int
     */
    public function tln_findnxstr($body, $offset, $needle)
    {
        $pos = strpos($body, $needle, $offset);

        if ($pos === false) {
            $pos = strlen($body);
        }

        return $pos;
    }

    /**
     * Esta función toma una expresión regular de estilo PCRE e intenta igualarla.
     * dentro de la cuerda.
     *
     * @param  string  $body
     * @param  int  $offset
     * @param  string  $reg
     * @return array|bool
     */
    public function tln_findnxreg($body, $offset, $reg)
    {
        $matches = $retarr = [];

        $preg_rule = '%^(.*?)('.$reg.')%s';

        preg_match($preg_rule, substr($body, $offset), $matches);

        if (! isset($matches[0]) || ! $matches[0]) {
            $retarr = false;
        } else {
            $retarr[0] = $offset + strlen($matches[1]);

            $retarr[1] = $matches[1];

            $retarr[2] = $matches[2];
        }

        return $retarr;
    }

    /**
     * Esta función busca la siguiente etiqueta.
     *
     * @param  string  $body
     * @param  int  $offset
     * @return array|bool
     */
    public function tln_getnxtag($body, $offset)
    {
        if ($offset > strlen($body)) {
            return false;
        }

        $lt = $this->tln_findnxstr($body, $offset, '<');

        if ($lt == strlen($body)) {
            return false;
        }

        /**
         * Estamos aquí:
         * bla, bla <etiqueta atributo="valor">
         * \---------^
         */
        $pos = $this->tln_skipspace($body, $lt + 1);

        if ($pos >= strlen($body)) {
            return [false, false, false, $lt, strlen($body)];
        }

        /**
         * Hay 3 tipos de etiquetas:
         * 1. Etiqueta de apertura, por ejemplo:
         *    <a href="bla">
         * 2. Etiqueta de cierre, por ejemplo:
         *    </a>
         * 3. Etiqueta sin contenido de estilo XHTML, por ejemplo:
         *    <img src="bla"/>
         */
        switch (substr($body, $pos, 1)) {
            case '/':
                $tagtype = 2;

                $pos++;
                break;

            case '!':
                /**
                 * Un comentario o una declaración SGML.
                 */
                if (substr($body, $pos + 1, 2) == '--') {
                    $gt = strpos($body, '-->', $pos);

                    if ($gt === false) {
                        $gt = strlen($body);
                    } else {
                        $gt += 2;
                    }

                    return [false, false, false, $lt, $gt];
                } else {
                    $gt = $this->tln_findnxstr($body, $pos, '>');

                    return [false, false, false, $lt, $gt];
                }
                break;

            default:
                $tagtype = 1;
                break;

        }

        /**
         * Busque el siguiente [\W-_], que indicará el final del nombre de la etiqueta.
         */
        $regary = $this->tln_findnxreg($body, $pos, '[^\w\-_]');

        if ($regary == false) {
            return [false, false, false, $lt, strlen($body)];
        }

        [$pos, $tagname, $match] = $regary;

        $tagname = strtolower($tagname);

        /**
         * $match can be either of these:
         * '>' indica el final de la etiqueta por completo.
         * '\s' indica el final del nombre de la etiqueta.
         * '/' indica que se trata de una etiqueta xhtml de tipo 3.
         *
         * Cualquier otra cosa que encontremos allí indica una etiqueta no válida.
         */
        switch ($match) {
            case '/':
                /**
                 * Esta es una etiqueta de estilo xhtml con un cierre / al final.
                 * final, así: <img src="blah"/>. Comprueba si se sigue
                 * por el corchete de cierre. Si no, entonces esta etiqueta no es válida.
                 */
                if (substr($body, $pos, 2) == '/>') {
                    $pos++;

                    $tagtype = 3;
                } else {
                    $gt = $this->tln_findnxstr($body, $pos, '>');

                    $retary = [false, false, false, $lt, $gt];

                    return $retary;
                }

                // caída intencional
            case '>':
                return [$tagname, false, $tagtype, $lt, $pos];

            default:
                /**
                 * Comprueba si son espacios en blanco.
                 */
                if (! preg_match('/\s/', $match)) {
                    /**
                     * ¡Esta es una etiqueta no válida! Busque el próximo cierre ">".
                     */
                    $gt = $this->tln_findnxstr($body, $lt, '>');

                    return [false, false, false, $lt, $gt];
                }

                break;
        }

        /**
         * En este punto estamos aquí:
         * <atributo de nombre de etiqueta = 'bla'>
         * \-------^
         *
         * En este punto hacemos un bucle para encontrar todos los atributos.
         */
        $attary = [];

        while ($pos <= strlen($body)) {
            $pos = $this->tln_skipspace($body, $pos);

            if ($pos == strlen($body)) {
                /**
                 * Etiqueta no cerrada.
                 */
                return [false, false, false, $lt, $pos];
            }

            /**
             * Ver si llegamos a un ">" o "/>", lo que significa que llegamos a
             * el final de la etiqueta.
             */
            $matches = [];

            if (preg_match('%^(\s*)(>|/>)%s', substr($body, $pos), $matches)) {
                /**
                 * Sí. Así lo hicimos.
                 */
                $pos += strlen($matches[1]);

                if ($matches[2] == '/>') {
                    $tagtype = 3;

                    $pos++;
                }

                return [$tagname, $attary, $tagtype, $lt, $pos];
            }

            /**
             * Hay varios tipos de atributos, con opciones opcionales.
             * [:espacio:] entre miembros.
             * Tipo 1:
             *   nombreatributo[:espacio:]=[:espacio:]'CDATA'
             * Tipo 2:
             *   nombreatributo[:espacio:]=[:espacio:]"CDATA"
             * Tipo 3:
             *   attr[:espacio:]=[:espacio:]CDATA
             * Tipo 4:
             *   nombre de atributo
             *
             * Dejamos los tipos 1 y 2 iguales, el tipo 3 verificamos
             * '"' y conviértalo a "&quot" si es necesario, luego envuélvalo
             * comillas dobles. Tipo 4 lo convertimos en:
             * attrname="sí".
             */
            $regary = $this->tln_findnxreg($body, $pos, '[^\w\-_]');

            if ($regary == false) {
                /**
                 * Parece que el cuerpo terminó antes del final de la etiqueta.
                 */
                return [false, false, false, $lt, strlen($body)];
            }

            [$pos, $attname, $match] = $regary;

            $attname = strtolower($attname);

            /**
             * Llegamos al final del nombre del atributo. Varias cosas posibles
             * aquí:
             * '>' significa el final de la etiqueta y este es el tipo de atributo 4
             * '/' si va seguido de '>' significa lo mismo que arriba
             * '\s' significa muchas cosas: mira lo que le sigue.
             *      cualquier otra cosa significa que el atributo no es válido.
             */
            switch ($match) {
                case '/':
                    /**
                     * Esta es una etiqueta de estilo xhtml con un cierre / al final.
                     * final, así: <img src="blah"/>. Comprueba si se sigue
                     * por el corchete de cierre. Si no, entonces esta etiqueta no es válida.
                     */
                    if (substr($body, $pos, 2) == '/>') {
                        $pos++;
                        $tagtype = 3;
                    } else {
                        $gt = $this->tln_findnxstr($body, $pos, '>');
                        $retary = [false, false, false, $lt, $gt];

                        return $retary;
                    }

                    // caída intencional
                case '>':
                    $attary[$attname] = '"yes"';

                    return [$tagname, $attary, $tagtype, $lt, $pos];
                    break;

                default:
                    /**
                     * Omita los espacios en blanco y vea a dónde llegamos.
                     */
                    $pos = $this->tln_skipspace($body, $pos);

                    $char = substr($body, $pos, 1);
                    /**
                     * Aquí son válidas dos cosas:
                     * '=' significa que este es el tipo de atributo 1 2 o 3.
                     * \w significa que este era el tipo de atributo 4.
                     * cualquier otra cosa la ignoramos y volvemos a repetir. Fin de la etiqueta y
                     * Los elementos no válidos serán detectados por nuestros controles al principio.
                     * del bucle.
                     */
                    if ($char == '=') {
                        $pos++;

                        $pos = $this->tln_skipspace($body, $pos);
                        /**
                         * Aquí hay 3 posibilidades:
                         * "'" atributo tipo 1
                         * '"' tipo de atributo 2
                         * todo lo demás es el contenido de la etiqueta tipo 3
                         */
                        $quot = substr($body, $pos, 1);

                        if ($quot == '\'') {
                            $regary = $this->tln_findnxreg($body, $pos + 1, '\'');

                            if ($regary == false) {
                                return [false, false, false, $lt, strlen($body)];
                            }

                            [$pos, $attval, $match] = $regary;

                            $pos++;

                            $attary[$attname] = '\''.$attval.'\'';
                        } elseif ($quot == '"') {
                            $regary = $this->tln_findnxreg($body, $pos + 1, '\"');

                            if ($regary == false) {
                                return [false, false, false, $lt, strlen($body)];
                            }

                            [$pos, $attval, $match] = $regary;

                            $pos++;

                            $attary[$attname] = '"'.$attval.'"';
                        } else {
                            /**
                             * Estos son odiosos. Busque \s o >.
                             */
                            $regary = $this->tln_findnxreg($body, $pos, '[\s>]');

                            if ($regary == false) {
                                return [false, false, false, $lt, strlen($body)];
                            }

                            [$pos, $attval, $match] = $regary;

                            $attval = preg_replace('/\"/s', '&quot;', $attval);

                            $attary[$attname] = '"'.$attval.'"';
                        }
                    } elseif (preg_match('|[\w/>]|', $char)) {
                        $attary[$attname] = '"yes"';
                    } else {
                        $gt = $this->tln_findnxstr($body, $pos, '>');

                        return [false, false, false, $lt, $gt];
                    }
                    break;
            }
        }

        /**
         * El hecho de que hayamos llegado aquí indica que el final de la etiqueta nunca fue
         * encontró. Devuelve una indicación de etiqueta no válida para que se elimine.
         */
        return [false, false, false, $lt, strlen($body)];
    }

    /**
     * Traduce entidades a valores literales para que puedan verificarse.
     *
     * @param  string  $attvalue
     * @param  string  $regex
     * @param  bool  $hex
     * @return bool
     */
    public function tln_deent(&$attvalue, $regex, $hex = false)
    {
        preg_match_all($regex, $attvalue, $matches);

        if (is_array($matches) && count($matches[0]) > 0) {
            $repl = [];

            for ($i = 0; $i < count($matches[0]); $i++) {
                $numval = $matches[1][$i];

                if ($hex) {
                    $numval = hexdec($numval);
                }

                $repl[$matches[0][$i]] = chr($numval);
            }

            $attvalue = strtr($attvalue, $repl);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Esta función verifica los valores de los atributos en busca de valores codificados por entidades.
     * y los devuelve traducidos a cadenas de 8 bits para que podamos ejecutar
     * controles sobre ellos.
     *
     * @param  string  $attvalue
     * @return void
     */
    public function tln_defang(&$attvalue)
    {
        /**
         * Omita esto si no hay símbolos comerciales ni barras invertidas.
         */
        if (strpos($attvalue, '&') === false
            && strpos($attvalue, '\\') === false
        ) {
            return;
        }

        do {
            $m = false;
            $m = $m || $this->tln_deent($attvalue, '/\&#0*(\d+);*/s');
            $m = $m || $this->tln_deent($attvalue, '/\&#x0*((\d|[a-f])+);*/si', true);
            $m = $m || $this->tln_deent($attvalue, '/\\\\(\d+)/s', true);
        } while ($m == true);

        $attvalue = stripslashes($attvalue);
    }

    /**
     * Elimina pestañas, nuevas líneas o retornos de carro. Our friends the
     * Los creadores del navegador con un valor de mercado del 95% decidieron que
     * Sería divertido hacer que "java[tab]script" sea tan bueno como "javascript".
     *
     * @param  string  $attvalue
     * @return void
     */
    public function tln_unspace(&$attvalue)
    {
        if (strcspn($attvalue, "\t\r\n\0 ") != strlen($attvalue)) {
            $attvalue = str_replace(
                ["\t", "\r", "\n", "\0", ' '],
                ['', '', '', '', ''],
                $attvalue
            );
        }
    }

    /**
     * Esta función ejecuta varias comprobaciones de los atributos.
     *
     * @param  string  $tagname
     * @param  array  $attary
     * @param  array  $rm_attnames
     * @param  array  $bad_attvals
     * @param  array  $add_attr_to_tag
     * @param  string  $trans_image_path
     * @param  bool  $block_external_images
     * @return array with modified attributes.
     */
    public function tln_fixatts(
        $tagname,
        $attary,
        $rm_attnames,
        $bad_attvals,
        $add_attr_to_tag,
        $trans_image_path,
        $block_external_images
    ) {
        /**
         * Convierta a matriz si no es así.
         */
        $attary = is_array($attary) ? $attary : [];

        foreach ($attary as $attname => $attvalue) {
            /**
             * Vea si este atributo debería eliminarse.
             */
            foreach ($rm_attnames as $matchtag => $matchattrs) {
                if (preg_match($matchtag, $tagname)) {
                    foreach ($matchattrs as $matchattr) {
                        if (preg_match($matchattr, $attname)) {
                            unset($attary[$attname]);

                            continue 2;
                        }
                    }
                }
            }

            $this->tln_defang($attvalue);

            $this->tln_unspace($attvalue);

            /**
             * Ahora ejecutemos comprobaciones de los valores de atributos.
             * No espero que nadie comprenda esto. Si lo haces,
             * ponte en contacto conmigo para que pueda conducir hasta donde vives y
             * estrecharle la mano personalmente. :)
             */
            foreach ($bad_attvals as $matchtag => $matchattrs) {
                if (preg_match($matchtag, $tagname)) {
                    foreach ($matchattrs as $matchattr => $valary) {
                        if (preg_match($matchattr, $attname)) {
                            [$valmatch, $valrepl] = $valary;

                            $newvalue = preg_replace($valmatch, $valrepl, $attvalue);

                            if ($newvalue != $attvalue) {
                                $attary[$attname] = $newvalue;
                                $attvalue = $newvalue;
                            }
                        }
                    }
                }
            }
        }

        /**
         * Vea si necesitamos agregar algún atributo a esta etiqueta.
         */
        foreach ($add_attr_to_tag as $matchtag => $addattary) {
            if (preg_match($matchtag, $tagname)) {
                $attary = array_merge($attary, $addattary);
            }
        }

        return $attary;
    }

    /**
     * Arreglar la URL.
     *
     * @return void
     */
    public function tln_fixurl($attname, &$attvalue, $trans_image_path, $block_external_images)
    {
        $sQuote = '"';

        $attvalue = trim($attvalue);

        if ($attvalue && ($attvalue[0] == '"' || $attvalue[0] == "'")) {
            // eliminar las comillas dobles
            $sQuote = $attvalue[0];

            $attvalue = trim(substr($attvalue, 1, -1));
        }

        /**
         * Reemplace las etiquetas src vacías con la imagen en blanco.  src solo se usa
         * para marcos, imágenes y entradas de imágenes.  Hacer un reemplazo debería
         * no afectará su funcionamiento como debería ser, sin embargo, se detendrá
         * IE se inicie cuando las etiquetas src para img no están configuradas.
         */
        if ($attvalue == '') {
            $attvalue = $sQuote.$trans_image_path.$sQuote;
        } else {
            // Primero, no permita caracteres de 8 bits ni caracteres de control.
            if (preg_match('/[\0-\37\200-\377]+/', $attvalue)) {
                switch ($attname) {
                    case 'href':
                        $attvalue = $sQuote.'http://invalid-stuff-detected.example.com'.$sQuote;
                        break;

                    default:
                        $attvalue = $sQuote.$trans_image_path.$sQuote;
                        break;

                }
            } else {
                $aUrl = parse_url($attvalue);

                if (isset($aUrl['scheme'])) {
                    switch (strtolower($aUrl['scheme'])) {
                        case 'mailto':
                        case 'http':
                        case 'https':
                        case 'ftp':
                            if ($attname != 'href') {
                                if ($block_external_images == true) {
                                    $attvalue = $sQuote.$trans_image_path.$sQuote;
                                } else {
                                    if (! isset($aUrl['path'])) {
                                        $attvalue = $sQuote.$trans_image_path.$sQuote;
                                    }
                                }
                            } else {
                                $attvalue = $sQuote.$attvalue.$sQuote;
                            }
                            break;

                        case 'outbind':
                            $attvalue = $sQuote.$attvalue.$sQuote;
                            break;

                        case 'cid':
                            $attvalue = $sQuote.$attvalue.$sQuote;
                            break;

                        default:
                            $attvalue = $sQuote.$trans_image_path.$sQuote;
                            break;
                    }
                } else {
                    if (! isset($aUrl['path']) || $aUrl['path'] != $trans_image_path) {
                        $$attvalue = $sQuote.$trans_image_path.$sQuote;
                    }
                }
            }
        }
    }

    /**
     * Arreglar estilo.
     *
     * @return void
     */
    public function tln_fixstyle($body, $pos, $trans_image_path, $block_external_images)
    {
        $me = 'tln_fixstyle';

        $content = '';

        $sToken = '';

        $bSucces = false;

        $bEndTag = false;

        for ($i = $pos,$iCount = strlen($body); $i < $iCount; $i++) {
            $char = $body[$i];

            switch ($char) {
                case '<':
                    $sToken = $char;
                    break;
                case '/':
                    if ($sToken == '<') {
                        $sToken .= $char;

                        $bEndTag = true;
                    } else {
                        $content .= $char;
                    }

                    break;

                case '>':
                    if ($bEndTag) {
                        $sToken .= $char;

                        if (preg_match('/\<\/\s*style\s*\>/i', $sToken, $aMatch)) {
                            $newpos = $i + 1;

                            $bSucces = true;

                            break 2;

                        } else {
                            $content .= $sToken;
                        }

                        $bEndTag = false;
                    } else {
                        $content .= $char;
                    }
                    break;
                case '!':
                    if ($sToken == '<') {
                        if (isset($body[$i + 2]) && substr($body, $i, 3) == '!--') {
                            $i = strpos($body, '-->', $i + 3);

                            if (! $i) {
                                $i = strlen($body);
                            }

                            $sToken = '';
                        }
                    } else {
                        $content .= $char;
                    }
                    break;
                default:
                    if ($bEndTag) {
                        $sToken .= $char;
                    } else {
                        $content .= $char;
                    }
                    break;
            }
        }

        if (! $bSucces) {
            return [false, strlen($body)];
        }

        /**
         * Primero busque la declaración general de estilo BODY, que sería
         * así:
         * cuerpo {fondo: bla-bla}
         * y cámbielo a .bodyclass para que podamos asignarlo a un <div>
         */
        $content = preg_replace("|body(\s*\{.*?\})|si", '.bodyclass\\1', $content);

        $trans_image_path = $trans_image_path;

        // primero verifique secuencias de 8 bits y caracteres de control no permitidos
        if (preg_match('/[\16-\37\200-\377]+/', $content)) {
            $content = '<!-- style block removed by html filter due to presence of 8bit characters -->';

            return [$content, $newpos];
        }

        // eliminar la línea @import
        $content = preg_replace("/^\s*(@import.*)$/mi", "\n<!-- @import rules forbidden -->\n", $content);

        $content = preg_replace('/(\\\\)?u(\\\\)?r(\\\\)?l(\\\\)?/i', 'url', $content);

        preg_match_all("/url\s*\((.+)\)/si", $content, $aMatch);

        if (count($aMatch)) {
            $aValue = $aReplace = [];

            foreach ($aMatch[1] as $sMatch) {
                $urlvalue = $sMatch;
                $this->tln_fixurl('style', $urlvalue, $trans_image_path, $block_external_images);
                $aValue[] = $sMatch;
                $aReplace[] = $urlvalue;
            }

            $content = str_replace($aValue, $aReplace, $content);
        }

        /**
         * Elimine las barras invertidas, las entidades y los espacios en blanco superfluos.
         */
        $contentTemp = $content;
        $this->tln_defang($contentTemp);
        $this->tln_unspace($contentTemp);

        $match = ['/\/\*.*\*\//',
            '/expression/i',
            '/behaviou*r/i',
            '/binding/i',
            '/include-source/i',
            '/javascript/i',
            '/script/i',
            '/position/i'];

        $replace = ['', 'idiocy', 'idiocy', 'idiocy', 'idiocy', 'idiocy', 'idiocy', ''];

        $contentNew = preg_replace($match, $replace, $contentTemp);

        if ($contentNew !== $contentTemp) {
            $content = $contentNew;
        }

        return [$content, $newpos];
    }

    /**
     * Cuerpo a div.
     *
     * @return void
     */
    public function tln_body2div($attary, $trans_image_path)
    {
        $me = 'tln_body2div';

        $divattary = ['class' => "'bodyclass'"];

        $has_bgc_stl = $has_txt_stl = false;

        $styledef = '';

        if (is_array($attary) && count($attary) > 0) {
            foreach ($attary as $attname => $attvalue) {
                $quotchar = substr($attvalue, 0, 1);

                $attvalue = str_replace($quotchar, '', $attvalue);

                switch ($attname) {
                    case 'background':
                        $styledef .= "background-image: url('$trans_image_path'); ";
                        break;

                    case 'bgcolor':
                        $has_bgc_stl = true;

                        $styledef .= "background-color: $attvalue; ";
                        break;

                    case 'text':
                        $has_txt_stl = true;

                        $styledef .= "color: $attvalue; ";
                        break;

                }
            }

            // Outlook define un color de fondo blanco y ningún color de texto. Esto puede generar texto blanco sobre un fondo blanco con ciertos temas.
            if ($has_bgc_stl && ! $has_txt_stl) {
                $styledef .= 'color: #000000; ';
            }

            if (strlen($styledef) > 0) {
                $divattary['style'] = "\"$styledef\"";
            }
        }

        return $divattary;
    }

    /**
     * Desinfectar.
     *
     * @param  string  $body
     * @param  array  $tag_list
     * @param  array  $rm_tags_with_content
     * @param  array  $self_closing_tags
     * @param  bool  $force_tag_closing
     * @param  array  $rm_attnames
     * @param  array  $bad_attvals
     * @param  array  $add_attr_to_tag
     * @param  string  $trans_image_path
     * @param  bool  $block_external_images
     * @return string
     */
    public function tln_sanitize(
        $body,
        $tag_list,
        $rm_tags_with_content,
        $self_closing_tags,
        $force_tag_closing,
        $rm_attnames,
        $bad_attvals,
        $add_attr_to_tag,
        $trans_image_path,
        $block_external_images
    ) {
        /**
         * Normalice rm_tags y rm_tags_with_content.
         */
        $rm_tags = array_shift($tag_list);

        @array_walk($tag_list, [$this, 'tln_casenormalize']);

        @array_walk($rm_tags_with_content, [$this, 'tln_casenormalize']);

        @array_walk($self_closing_tags, [$this, 'tln_casenormalize']);

        /**
         * Vea si tag_list contiene etiquetas para eliminar o etiquetas para permitir.
         * falso significa eliminar estas etiquetas
         * verdadero significa permitir estas etiquetas
         */
        $curpos = 0;
        $open_tags = [];
        $trusted = '';
        $skip_content = false;

        /**
         * Ocúpate de las estúpidas entidades javascript de Netscape como
         * &{alerta('boo')};
         */
        $body = preg_replace('/&(\{.*?\};)/si', '&amp;\\1', $body);

        while (($curtag = $this->tln_getnxtag($body, $curpos)) != false) {
            [$tagname, $attary, $tagtype, $lt, $gt] = $curtag;
            $free_content = substr($body, $curpos, $lt - $curpos);

            /**
             * Cuida de <estilo>.
             */
            if ($tagname == 'style' && $tagtype == 1) {
                [$free_content, $curpos] =
                    $this->tln_fixstyle($body, $gt + 1, $trans_image_path, $block_external_images);

                if ($free_content != false) {
                    if (! empty($attary)) {
                        $attary = $this->tln_fixatts($tagname,
                            $attary,
                            $rm_attnames,
                            $bad_attvals,
                            $add_attr_to_tag,
                            $trans_image_path,
                            $block_external_images
                        );
                    }

                    $trusted .= $this->tln_tagprint($tagname, $attary, $tagtype);

                    if (isset($this->$free_content)) {
                        $trusted .= $this->$free_content;
                    }

                    $trusted .= $this->tln_tagprint($tagname, false, 2);
                }

                continue;
            }

            if ($skip_content == false) {
                $trusted .= $free_content;
            }

            if ($tagname != false) {
                if ($tagtype == 2) {
                    if ($skip_content == $tagname) {
                        $tagname = false;

                        $skip_content = false;
                    } else {
                        if ($skip_content == false) {
                            if ($tagname == 'body') {
                                $tagname = 'div';
                            }

                            if (isset($open_tags[$tagname]) &&
                                $open_tags[$tagname] > 0
                            ) {
                                $open_tags[$tagname]--;
                            } else {
                                $tagname = false;
                            }
                        }
                    }
                } else {
                    if (! $skip_content) {
                        if (
                            $tagtype == 1
                            && in_array($tagname, $self_closing_tags)
                        ) {
                            $tagtype = 3;
                        }

                        /**
                         * Veamos si debemos omitir esta etiqueta y cualquier contenido.
                         * dentro de él.
                         */
                        if (
                            $tagtype == 1
                            && in_array($tagname, $rm_tags_with_content)
                        ) {
                            $skip_content = $tagname;
                        } else {
                            if ((
                                ! $rm_tags
                                && in_array($tagname, $tag_list))
                                || (
                                    $rm_tags
                                    && ! in_array($tagname, $tag_list)
                                )
                            ) {
                                $tagname = false;
                            } else {
                                /**
                                 * Convierte el cuerpo en div.
                                 */
                                if ($tagname == 'body') {
                                    $tagname = 'div';
                                    $attary = $this->tln_body2div($attary, $trans_image_path);
                                }

                                if ($tagtype == 1) {
                                    if (isset($open_tags[$tagname])) {
                                        $open_tags[$tagname]++;
                                    } else {
                                        $open_tags[$tagname] = 1;
                                    }
                                }

                                /**
                                 * Aquí es donde realizamos otras comprobaciones.
                                 */
                                if (is_array($attary) && count($attary) > 0) {
                                    $attary = $this->tln_fixatts(
                                        $tagname,
                                        $attary,
                                        $rm_attnames,
                                        $bad_attvals,
                                        $add_attr_to_tag,
                                        $trans_image_path,
                                        $block_external_images
                                    );
                                }
                            }
                        }
                    }
                }

                if ($tagname != false && $skip_content == false) {
                    $trusted .= $this->tln_tagprint($tagname, $attary, $tagtype);
                }
            }

            $curpos = $gt + 1;
        }

        $trusted .= substr($body, $curpos, strlen($body) - $curpos);

        if ($force_tag_closing == true) {
            foreach ($open_tags as $tagname => $opentimes) {
                while ($opentimes > 0) {
                    $trusted .= '</'.$tagname.'>';

                    $opentimes--;
                }
            }
            $trusted .= "\n";
        }

        return $trusted;
    }

    /**
     * Filtro HTML.
     *
     * @param  bool  $block_external_images
     * @return void
     */
    public function process($body, $trans_image_path, $block_external_images = false)
    {
        $tag_list = [
            false,
            'object',
            'meta',
            'html',
            'head',
            'base',
            'link',
            'frame',
            'iframe',
            'plaintext',
            'marquee',
        ];

        $rm_tags_with_content = [
            'script',
            'applet',
            'embed',
            'title',
            'frameset',
            'xmp',
            'xml',
        ];

        $self_closing_tags = [
            'img',
            'br',
            'hr',
            'input',
            'outbind',
        ];

        $force_tag_closing = true;

        $rm_attnames = [
            '/.*/' => [
                '/^on.*/i',
                '/^dynsrc/i',
                '/^data.*/i',
                '/^lowsrc.*/i',
            ],
        ];

        $bad_attvals = [
            '/.*/' => [
                '/^src|background/i' => [
                    [
                        '/^([\'"])\s*\S+script\s*:.*([\'"])/si',
                        '/^([\'"])\s*mocha\s*:*.*([\'"])/si',
                        '/^([\'"])\s*about\s*:.*([\'"])/si',
                    ], [
                        "\\1$trans_image_path\\2",
                        "\\1$trans_image_path\\2",
                        "\\1$trans_image_path\\2",
                    ],
                ],

                '/^href|action/i' => [
                    [
                        '/^([\'"])\s*\S+script\s*:.*([\'"])/si',
                        '/^([\'"])\s*mocha\s*:*.*([\'"])/si',
                        '/^([\'"])\s*about\s*:.*([\'"])/si',
                    ], [
                        '\\1#\\1',
                        '\\1#\\1',
                        '\\1#\\1',
                    ],
                ],
            ],
        ];

        if ($block_external_images) {
            array_push(
                $bad_attvals['/.*/']['/^src|background/i'][0],
                '/^([\'\"])\s*https*:.*([\'\"])/si'
            );

            array_push(
                $bad_attvals['/.*/']['/^src|background/i'][1],
                "\\1$trans_image_path\\1"
            );
        }

        $add_attr_to_tag = [
            '/^a$/i' => ['target' => '"_blank"'],
        ];

        $trusted = $this->tln_sanitize(
            $body,
            $tag_list,
            $rm_tags_with_content,
            $self_closing_tags,
            $force_tag_closing,
            $rm_attnames,
            $bad_attvals,
            $add_attr_to_tag,
            $trans_image_path,
            $block_external_images
        );

        return $trusted;
    }
}
