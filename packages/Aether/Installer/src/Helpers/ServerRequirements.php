<?php

namespace Aether\Installer\Helpers;

class ServerRequirements
{
    /**
     * Versión mínima de PHP admitida (la anulación está en el archivo de configuración installer.php).
     *
     * @var string
     */
    private $minPhpVersion = '8.1.0';

    /**
     * Verifique los requisitos del servidor.
     */
    public function validate(): array
    {
        // Requisitos del servidor
        $requirements = [
            'php' => [
                'calendar',
                'ctype',
                'curl',
                'dom',
                'fileinfo',
                'filter',
                'gd',
                'hash',
                'intl',
                'json',
                'mbstring',
                'openssl',
                'pcre',
                'pdo',
                'session',
                'tokenizer',
                'xml',
            ],
        ];

        $results = [];

        foreach ($requirements as $type => $requirement) {
            foreach ($requirement as $item) {
                $results['requirements'][$type][$item] = true;

                if (! extension_loaded($item)) {
                    $results['requirements'][$type][$item] = false;

                    $results['errors'] = true;
                }
            }
        }

        return $results;
    }

    /**
     * Verifique el requisito de la versión de PHP.
     *
     * @return array
     */
    public function checkPHPversion(?string $minPhpVersion = null)
    {
        $minVersionPhp = $minPhpVersion ?? $this->minPhpVersion;

        $currentPhpVersion = $this->getPhpVersionInfo();

        $supported = version_compare($currentPhpVersion['version'], $minVersionPhp) >= 0;

        return [
            'full' => $currentPhpVersion['full'],
            'current' => $currentPhpVersion['version'],
            'minimum' => $minVersionPhp,
            'supported' => $supported,
        ];
    }

    /**
     * Obtenga información de la versión actual de PHP.
     *
     * @return array
     */
    private static function getPhpVersionInfo()
    {
        $currentVersionFull = PHP_VERSION;

        preg_match("#^\d+(\.\d+)*#", $currentVersionFull, $filtered);

        return [
            'full' => $currentVersionFull,
            'version' => $filtered[0] ?? $currentVersionFull,
        ];
    }
}
