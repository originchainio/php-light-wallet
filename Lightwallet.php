<?php
/*
The MIT License (MIT)
Copyright (C) 2019 OriginchainDev

originchain.net

　　Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the "Software"),
to deal in the Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish, distribute, sublicense,
and/or sell copies of the Software, and to permit persons to whom the Software
is furnished to do so, subject to the following conditions:
　　
　　The above copyright notice and this permission notice shall be included in all copies
or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE
AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// version: 20190228
class Lightwallet{
	private static $m=array(
		//wallet
		'createwallet'=>[],
		'getpublickeybyaddress'=>['address'],
		'getpublickeybyalias'=>['alias'],
		'getaliasbyaddress'=>['address'],
		'getaliasbypublickey'=>['publickey'],
		'getaddressesbyalias'=>['alias'],
		'getaddressesbypublickey'=>['publickey'],
		'getaddressinfo'=>['address'],
		'getbalance'=>['address_or_publickey'],
		'listlockunspent'=>['publickey'],
		'checkalias'=>['alias'],
		'sendtoaddressbyprivatekey'=>['fromaddress','toaddress','privatekey','amount'],
		'sendtoaliasbyprivatekey'=>['fromaddress','alias','privatekey','amount'],
		'registaliasbyprivatekey'=>['fromaddress','privatekey','alias'],
		//Blockchain
		'getblock'=>['blockhash'],
		'getblockchaininfo'=>[],
		'getblockcount'=>[],
		'getblockhash'=>['height'],
		'getblockstats'=>['hash_or_height'],
		'getchaintips'=>[],
		'getdifficulty'=>[],
		'getmempoolentry'=>['txid'],
		'getmempoolsize'=>[],
		'getrawmempool'=>[],
		'gettxout'=>['txid'],
		//Mining
		'getmininginfo'=>[],
		//Network
		'getversion'=>[],

	);
	private static $m_s=array(
		//$this->signature($dst,$val,$fee,$version,$message,$date,$public_key,$private_key);
		'sendtoaddress'=>['toaddress','amount','public_key','private_key'],
		'sendtoalias'=>['toalias','amount','public_key','private_key'],
		'registalias'=>['alias','public_key','private_key'],
	);
	private static $explain=array(
		'sendtoaddress'=>'"Local signature"',
		'sendtoalias'=>'"Local signature"',
		'registalias'=>'"Local signature"',

		'sendtoaddressbyprivatekey'=>'"Masternode signature,The private key is sent to the node"',
		'sendtoaliasbyprivatekey'=>'"Masternode signature,The private key is sent to the node"',
		'registaliasbyprivatekey'=>'"Masternode signature,The private key is sent to the node"',
	);
	function __construct(){
		
	}
	public function index_s($node_host,$method){
		global $argv;
		//
		if ($method=='registalias') {
			if (!isset($argv[3]) and !isset($argv[4]) and !isset($argv[5])) {
				echo 'error parameter';
				exit;
			}
		}else{
			if (!isset($argv[3]) and !isset($argv[4]) and !isset($argv[5]) and !isset($argv[6])) {
				echo 'error parameter';
				exit;
			}	
		}

		//
		switch ($method) {
			case 'sendtoaddress':
				$version=1;
				$tt=time();

				$toaddress=$argv[3];
				$amount=$argv[4];
				$public_key=$argv[5];
				$private_key=$argv[6];
				$fromaddress=$this->get_address_from_public_key($public_key);
				if ($fromaddress==false) {
					echo 'public_key is false or base58_encode fails';
				}

				$fee=$amount*0.005;
				$signature=$this->signature($toaddress,$amount,$fee,$version,'',$tt,$public_key,$private_key);
				//
				$part=array(
					'fromaddress' => $fromaddress,
					'toaddress' => $toaddress,
					'signature' => $signature,
					'amount' => $amount,
					'tt' => $tt,
					 );


				$res=$this->peer_post($node_host.'/Uinterface.php?='.$method, $part, 60);
				echo_array($res);
				break;
			case 'sendtoalias':
				$version=2;
				$tt=time();

				$alias=$argv[3];
				$amount=$argv[4];
				$public_key=$argv[5];
				$private_key=$argv[6];
				$fromaddress=$this->get_address_from_public_key($public_key);
				if ($fromaddress==false) {
					echo 'public_key is false or base58_encode fails';
				}

				$fee=$amount*0.005;
				$signature=$this->signature($alias,$amount,$fee,$version,'',$tt,$public_key,$private_key);
				//
				$part=array(
					'fromaddress' => $fromaddress,
					'alias' => $alias,
					'signature' => $signature,
					'amount' => $amount,
					'tt' => $tt,
					 );


				$res=$this->peer_post($node_host.'/Uinterface.php?='.$method, $part, 60);
				echo_array($res);
				break;
			case 'registalias':
				$version=3;
				$tt=time();

				$alias=$argv[3];
				$public_key=$argv[4];
				$private_key=$argv[5];
				$fromaddress=$this->get_address_from_public_key($public_key);
				if ($fromaddress==false) {
					echo 'public_key is false or base58_encode fails';
				}

				$amount=0;
				$fee=10;
				$signature=$this->signature($fromaddress,$amount,$fee,$version,$alias,$tt,$public_key,$private_key);
				//
				$part=array(
					'fromaddress' => $fromaddress,
					'signature' => $signature,
					'alias' => $alias,
					'tt' => $tt,
					 );


				$res=$this->peer_post($node_host.'/Uinterface.php?='.$method, $part, 60);
				echo_array($res);
				break;
			default:
				echo 'error method';
				exit;
				break;
		}
		exit;
	}
	public function index(){
		global $argv;
		if (!isset($argv[1])) { $this->info(); exit;   }
		$node_host=trim($argv[1]);
		if (!isset($argv[2])) { $this->info(); exit;   }
		$method=trim($argv[2]);
		if (!isset(self::$m[$method]) or !isset(self::$m_s[$method])) {
			$this->info(); exit;
		}
		//
		if (array_key_exists($method,self::$m_s)) {
			$this->index_s($node_host,$method);
			exit;
		}
		//
		$p=[];
		foreach ($argv as $key => $value) {
			if ($key>=3) {
				$p[]=trim($value);
			}
		}
		//
		$part=[];
		foreach (self::$m[$method] as $key => $value) {
			$part[$value]=$p[$key];
		}
		//
		if (count($p)!==count(self::$m[$method])) {
			echo 'method error';	exit;
		}
		//
		$res=$this->peer_post($node_host.'/Uinterface.php?='.$method, $part, 60);
		echo_array($res);

	}

	public function info(){
        echo "============================\n";
        echo "== Orc Light PHP Wallet  ==\n";
        echo "== www.originchain.net    ==\n";
        echo "============================\n\n";
		echo 'command:'."\n\n";
		foreach (self::$m_s as $key => $value) {
			if (count($value)==0) {
				echo '  '.$key;
			}else{
				echo '  '.$key.'  <'.implode("> <", $value).">";
			}
			
			if (isset(self::$explain[$key]) and self::$explain[$key]!=='' and self::$explain[$key]!==NULL) {
				echo "\n    ".self::$explain[$key];
			}
			echo "\n\n";
		}
		foreach (self::$m as $key => $value) {
			if (count($value)==0) {
				echo '  '.$key;
			}else{
				echo '  '.$key.'  <'.implode("> <", $value).">";
			}

			if (isset(self::$explain[$key]) and self::$explain[$key]!=='' and self::$explain[$key]!==NULL) {
				echo "\n    ".self::$explain[$key];
			}
			echo "\n\n";
		}
	}

	//sign
	public function signature($dst,$val,$fee,$version,$message,$date,$public_key, $private_key){
	    $val=number_format($val, 8, '.', '');
	    $fee=number_format($fee, 8, '.', '');
	    $info = "{$dst}-{$val}-{$fee}-{$version}-{$message}-{$date}-{$public_key}";
	    $signature = ec_sign($info, $private_key);
	    return $signature;
	}
    public function get_address_from_public_key($public_key){
        $public_key=$public_key;
        for ($i = 0; $i < 9; $i++) {
            $public_key = hash('sha512', $public_key, true);
        }
        $public_key = base58_encode($public_key);
        if (valid_base58($public_key)==true and valid_len($public_key,70,128)==true) {
            return $public_key;
        }else{
            return false;
        } 
    }
    public function peer_post($url, $data = [], $timeout = 60){
        if ($timeout==='') {
            $timeout=60;
        }
        $postdata = http_build_query(
            [
                'data' => json_encode($data),
                "coin" => 'origin',
            ]
        );

        $opts = [
            'http' =>
                [
                    'timeout' => $timeout,
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata,
                ],
        ];

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        if ($result==false) {
            echo 'result error';
            return false;
        }
        $res = json_decode($result, true);

        // the function will return false if something goes wrong
        if ($res['status'] == "ok" || $res['coin'] == 'origin') {
            return $res['data'];
        }else{
            echo 'result error';
            return false;
        }  
    }
}
function echo_array($a) { echo "<pre>"; print_r($a); echo "</pre>"; }
date_default_timezone_set("UTC");
$Lightwallet=new Lightwallet();
$Lightwallet->index();
?>