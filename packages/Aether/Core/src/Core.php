<?php

namespace Aether\Core;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Aether\Core\Repositories\CoreConfigRepository;
use Aether\Core\Repositories\CountryRepository;
use Aether\Core\Repositories\CountryStateRepository;

class Core
{
    /**
     * La versión CRM v1.
     *
     * @var string
     */
    const AETHER_VERSION = '2.2.2';

    /**
     * Crea una nueva instancia.
     *
     * @return void
     */
    public function __construct(
        protected CountryRepository $countryRepository,
        protected CoreConfigRepository $coreConfigRepository,
        protected CountryStateRepository $countryStateRepository
    ) {
    }

    /**
     * Obtenga el número de versión de CRM v1.
     *
     * @return string
     */
    public function version()
    {
        return static::AETHER_VERSION;
    }

    /**
     * Recupera todas las zonas horarias.
     */
    public function timezones(): array
    {
        $timezones = [];

        foreach (timezone_identifiers_list() as $timezone) {
            $timezones[$timezone] = $timezone;
        }

        return $timezones;
    }

    /**
     * Recupera todas las configuraciones regionales.
     */
    public function locales(): array
    {
        $options = [];

        foreach (config('app.available_locales') as $key => $title) {
            $options[] = [
                'title' => $title,
                'value' => $key,
            ];
        }

        return $options;
    }

    /**
     * Recuperar todos los países.
     *
     * @return Collection
     */
    public function countries()
    {
        return $this->countryRepository->all();
    }

    /**
     * Devuelve el nombre del país por código.
     */
    public function country_name(string $code): string
    {
        $country = $this->countryRepository->findOneByField('code', $code);

        return $country ? $country->name : '';
    }

    /**
     * Devuelve el nombre del estado por código.
     */
    public function state_name(string $code): string
    {
        $state = $this->countryStateRepository->findOneByField('code', $code);

        return $state ? $state->name : $code;
    }

    /**
     * Recupera todos los estados del país.
     *
     * @return Collection
     */
    public function states(string $countryCode)
    {
        return $this->countryStateRepository->findByField('country_code', $countryCode);
    }

    /**
     * Recupera todos los estados agrupados por código de país.
     *
     * @return Collection
     */
    public function groupedStatesByCountries()
    {
        $collection = [];

        foreach ($this->countryStateRepository->all() as $state) {
            $collection[$state->country_code][] = $state->toArray();
        }

        return $collection;
    }

    /**
     * Recupera todos los estados agrupados por código de país.
     *
     * @return Collection
     */
    public function findStateByCountryCode($countryCode = null, $stateCode = null)
    {
        $collection = [];

        $collection = $this->countryStateRepository->findByField([
            'country_code' => $countryCode,
            'code' => $stateCode,
        ]);

        if (count($collection)) {
            return $collection->first();
        } else {
            return false;
        }
    }

    /**
     * Cree un objeto único a través de una sola fachada.
     *
     * @param  string  $className
     * @return mixed
     */
    public function getSingletonInstance($className)
    {
        static $instances = [];

        if (array_key_exists($className, $instances)) {
            return $instances[$className];
        }

        return $instances[$className] = app($className);
    }

    /**
     * Fecha de formato
     *
     * @return string
     */
    public function formatDate($date, $format = 'd M Y h:iA')
    {
        return Carbon::parse($date)->format($format);
    }

    /**
     * Rango de semanas.
     *
     * @param  string  $date
     * @param  int  $day
     * @return string
     */
    public function xWeekRange($date, $day)
    {
        $ts = strtotime($date);

        if (!$day) {
            $start = (date('D', $ts) == 'Sun') ? $ts : strtotime('last sunday', $ts);

            return date('Y-m-d', $start);
        } else {
            $end = (date('D', $ts) == 'Sat') ? $ts : strtotime('next saturday', $ts);

            return date('Y-m-d', $end);
        }
    }

    /**
     * Devuelve el símbolo de moneda del código de moneda.
     *
     * @param  float  $price
     * @return string
     */
    public function currencySymbol($code)
    {
        $formatter = new \NumberFormatter('en@currency=' . $code, \NumberFormatter::CURRENCY);

        return $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
    }

    /**
     * Formatear el precio con el símbolo de moneda base. Este método también brinda la capacidad de codificar.
     * el símbolo de moneda base y su opcional.
     *
     * @param  float  $price
     * @return string
     */
    public function formatBasePrice($price)
    {
        if (is_null($price)) {
            $price = 0;
        }

        $formatter = new \NumberFormatter('en', \NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($price, config('app.currency'));
    }

    /**
     * Obtenga el campo de configuración.
     */
    public function getConfigField(string $fieldName): ?array
    {
        return system_config()->getConfigField($fieldName);
    }

    /**
     * Recuperar información para la configuración.
     */
    public function getConfigData(string $field): mixed
    {
        return system_config()->getConfigData($field);
    }
}
