<?php
namespace Fuel\Tasks;

use Goutte\Client as Client;
class Tsutaya_Discas
{
	// const ENG_URL_FORMAT = "http://www.discas.net/netdvd/dvd/searchDvd.do?pT=0&t=2&g=00001&pa=g_&dm=1&srt=%d&pn=%d";
	const ENG_URL_FORMAT = "http://www.discas.net/netdvd/dvd/searchDvd.do?pT=0&pn=1&t=2&dm=1&pa=g_&srt=1&g=00001";

	public static function run(){
		$client = new Client();
		$url = static::_make_url(1,1);
		print $url;
		$client->setHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.52 Safari/537.36	');
		// $crawler = $client->request('GET', $url);
		$crawler = $client->request('GET', "http://www.discas.net/netdvd/dvd/searchDvd.do?pT=0&pn=1&t=2&dm=1&pa=g_&srt=1&g=00001");
		print_r($crawler->html());
		// $text = $crawler->filter('.searchWpInMain table th > a')->text();

		// print_r($text);
	}

	private static function _make_url($sort,$page){
		return sprintf(self::ENG_URL_FORMAT,$sort,$page);
	}
	public static function protect()
	{
	}
}

/* End of file */
