<?php
namespace Fuel\Tasks;

use Goutte\Client as Client;
class Geo
{
	const URL_FORMAT = "http://rental.geo-online.co.jp/search2/q/-dg-64/c-dvd/d-desc/o-rating/p-%d/";

	public static function run($cnt){
		$cinemas = [];
		while (true) {
			$url = static::_make_url(++$cnt);
			print $url.PHP_EOL;
			ob_flush();
			$crawler = static::_get_crawler($url);

			$lists = $crawler->filter('.imageBox')->each(function($node){

			    $img_url = $node->filter('a > img')->attr("src");
			    $text = $node->filter(".text > a")->text();
			    $href = $node->filter(".text > a")->attr('href');
			    return [
			    	"text" => str_replace("【Blu-ray】", "", $text),
			    	"href" => $href,
			    	"img" => $img_url,
			    	];
			});
	
			$query = \DB::insert('cinemas')->columns([
				'url',
				'title',
				'img',
				]);
			$execute = false;
			foreach ($lists as $list) {
				$cinema_cnt = \DB::select()->from('cinemas')->where('title',$list['text'])->execute()->count();
				if($cinema_cnt>= 1 || in_array($list['text'], $cinemas)){
					continue;
				}
				$query->values([
						$list['href'],
						$list['text'],
						$list['img'],
					]);
				$cinemas[] = $list['text'];
				$execute = true;
			}
			if($execute){
				$query->execute();
			}

		}
	}
	private static function _get_crawler($url){
		try {
			$config = array('timeout' => 120);
			$client = new Client();
			$client->setHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.52 Safari/537.36	');
			$crawler = $client->request('GET', $url);

			return $crawler;
		} catch (GuzzleHttp\Exception\ConnectException $e) {
			sleep(60);
			return static::_get_crawler($url);
		}
	}
	private static function _make_url($page){
		print $page;
		return sprintf(self::URL_FORMAT,$page);
	}

	public static function run_detail(){
		$records = \DB::select()->from('cinemas')
		->order_by('id')->execute();
		foreach ($records as $record) {
			$url = "http://rental.geo-online.co.jp".$record['url'];
			$page_cnt = \DB::select()->from('pages')->where('url',$url)->execute()->count();
			if($page_cnt >= 1){
				continue;
			}
			$crawler = static::_get_crawler($url);
			$html = $crawler->html();
			#productDetailText > table > tbody > tr:nth-child(1) > td > a
			#productDetailText > table > tbody > tr:nth-child(2) > td > a
			#productDetailText > table > tbody > tr:nth-child(3) > td > a
			#productDetailText > div.t10.px12
			#detailListLeft > table > tbody > tr:nth-child(1) > td:nth-child(3) > span
			#detailListLeft > table > tbody > tr:nth-child(2) > td:nth-child(3) > a
			\DB::insert('pages')->columns(['url','html','type'])->values([$url,$html,1])
			->execute();
		}

	}	
}

/* End of file */
