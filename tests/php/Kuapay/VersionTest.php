<?php
class Kuapay_VersionTest extends PHPUnit_Framework_TestCase {
    public function testLibraryIsVersioned() {
        $this->assertNotEmpty(Kuapay_Version::SDK_VERSION);
        $this->assertNotEmpty(Kuapay_Version::API_VERSION);

        $version = new Kuapay_Version();
        $this->assertNotEmpty($version::SDK_VERSION);
        $this->assertNotEmpty($version::API_VERSION);
    }
}