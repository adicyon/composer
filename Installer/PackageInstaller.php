<?php

namespace Apply\Composer\Installer;

use InvalidArgumentException;
use Composer\Config;
use Composer\Package\PackageInterface;

class PackageInstaller extends Installer
{
    /**
     * Decides if the installer supports the given type
     *
     * @param  string $packageType
     * @return bool
     */
    public function supports($packageType): bool
    {
        return $packageType === 'apply';
    }

    /**
     * Returns the installation path of a package
     *
     * @param  PackageInterface $package
     * @return string           path
     */
    public function getInstallPath(PackageInterface $package): string
    {
        // get the extra configuration of the top-level package
        if ($rootPackage = $this->composer->getPackage()) {
            $extra = $rootPackage->getExtra();
        } else {
            $extra = [];
        }

        // use path from configuration, otherwise fall back to default
        if (isset($extra['apply-library-path'])) {
            $path = $extra['apply-library-path'];
        } else {
            $path = $extra['apply']['library']['path'] ?? 'common/library';
        }

        // if explicitly set to something invalid (e.g. `false`), install to vendor dir
        if (!is_string($path)) {
            return parent::getInstallPath($package);
        }

        // don't allow unsafe directories
        $vendorDir = $this->composer->getConfig()->get('vendor-dir', Config::RELATIVE_PATHS) ?? 'vendor';
        if ($path === $vendorDir || $path === '.') {
            throw new InvalidArgumentException('The path ' . $path . ' is an unsafe installation directory for ' . $package->getPrettyName() . '.');
        }

        return $path.'/'.$package->getPrettyName();
    }
}
