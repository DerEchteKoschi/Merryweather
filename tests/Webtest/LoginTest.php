<?php

namespace Webtest;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group webtest
 */
class LoginTest extends WebTestCase
{

    public function testLoginFail()
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $client->followRedirect();
        self::assertRouteSame('app_login');
        self::assertResponseStatusCodeSame(200);
        $client->submitForm('Login', [
            '_username' => 'unknown',
            '_password' => 'unknown',
        ]);
        $client->followRedirect();
        self::assertRouteSame('app_login');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('div.alert', 'Login fehlgeschlagen. Haben sie alles korrekt eingegeben?');
    }

    public function testLoginSuccess()
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $client->followRedirect();

        self::assertResponseStatusCodeSame(200);
        self::assertRouteSame('app_login');

        $client->submitForm('Login', [
            '_username' => '1234',
            '_password' => 'user1',
        ]);
        $client->followRedirect();
        $client->followRedirect();
        self::assertResponseStatusCodeSame(200);
        self::assertRouteSame('app_slots');

        self::assertSelectorTextContains('ul > li:nth-child(1) > a', 'Verteilung');
        self::assertSelectorTextContains('ul > li:nth-child(2) > a', 'user1 (1234)');

    }
}
