<?php

declare(strict_types=1);

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverPlatform;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail() && ! static::usingSeleniumGrid()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $driverUrl = $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515';

        return match ($this->browserName()) {
            'firefox' => RemoteWebDriver::create($driverUrl, $this->firefoxCapabilities()),
            'edge' => RemoteWebDriver::create($driverUrl, $this->edgeCapabilities()),
            default => RemoteWebDriver::create($driverUrl, $this->chromeCapabilities()),
        };
    }

    /**
     * Determine the browser under test from the BROWSER environment variable.
     */
    protected function browserName(): string
    {
        return strtolower($_ENV['BROWSER'] ?? env('BROWSER') ?? 'chrome');
    }

    /**
     * Determine if the tests run against a Selenium standalone grid.
     */
    protected static function usingSeleniumGrid(): bool
    {
        return isset($_ENV['BROWSER']) || isset($_ENV['DUSK_DRIVER_URL']);
    }

    /**
     * Build the desired capabilities for Google Chrome.
     */
    protected function chromeCapabilities(): DesiredCapabilities
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--ignore-certificate-errors',
            '--allow-insecure-localhost',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY, $options
        );
    }

    /**
     * Build the desired capabilities for Mozilla Firefox.
     */
    protected function firefoxCapabilities(): DesiredCapabilities
    {
        return DesiredCapabilities::firefox();
    }

    /**
     * Build the desired capabilities for Microsoft Edge.
     */
    protected function edgeCapabilities(): DesiredCapabilities
    {
        return DesiredCapabilities::microsoftEdge()->setPlatform(WebDriverPlatform::ANY);
    }
}
