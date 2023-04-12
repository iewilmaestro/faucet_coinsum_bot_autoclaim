<?php
/**
 * server-bot
 *
 * @see        https://github.com/iewilmaestro/faucet_coinsum_bot_autoclaim
 * @author     iewilmaestro <purna.iera@gmail.com>
 */

/* ----MODUL STANDART---- */
function Curl($url, $header = 0, $post = 0,$c = 0) {
	while(true){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_COOKIE,TRUE);
		if($post) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		curl_setopt($ch, CURLOPT_HEADER, true);
		$r = curl_exec($ch);
		$c = curl_getinfo($ch);
		if(!$c) return "Curl Error : ".curl_error($ch); else{
			$hd = substr($r, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
			$bd = substr($r, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
			curl_close($ch);
			//jika body html tidak ada respon
			if(!$bd){
				print "Check your Connection!";
				sleep(2);
				print "\r                    \r";
				continue;
			}
			return array($hd,$bd);
		}
	}
}
//metode get
function Get($url){
	return curl($url, headers())[1];
}
//metode post
function Post($url,$data){
	return curl($url, headers(), $data)[1];
}
//header request
function headers(){
	// global untuk memanggil variable di luar fungsi
	// pastikan variable yang di panggil adalah variable uniq (tidak boleh ada lebih dari 1 variable yang sama di luar fungsi)
	global $cookie,$user_agent;
	//yang paling simple hanya membutuhkan cookie dan useragent
	$h[] = "cookie: ".$cookie;
	$h[] = "user-agent: ".$user_agent;
	return $h;
}

/* ----DATA YANG DI BUTUHKAN---- */
//  untuk mengisi headers
$cookie = "xxx";
$user_agent = "xxx";

//untuk menghilangkan tampilan error
error_reporting(0);

// untuk membersihkan monitor untuk linux (tidak berlaku untuk selain linux)
system("clear");


/* ----CHECK STATUS AKUN---- */

// dashboard
$result = get("https://faucet-coinsum.online/dashboard");
// explode: untuk mengampil data yang di butuhkan
// $address1 = explode('placeholder="Connect Your FaucetPay Email" value="',$result);
// menghasilkan result sebuah array, cara melihat hasil dengan print_r($adress1);
// lalu melanjutkan explode ke 2 untuk mengambil data yang di butuhkan
// $address = explode('">',$address1[1])[0];
// atau bisa menggunakan cara di bawah ini
$address = explode('">',explode('placeholder="Connect Your FaucetPay Email" value="',$result)[1])[0];

// untuk cek address sudah di set atau belum
if(!$address){ // jika $address tidak ada
	print "Cookie Expired\n";
	exit;
}

// jika adress ada maka akan lanjut ke sini
// menampilkan $adress
print "Address: ".$address."\n";


/* ---PROSES AUTO CLAIM COIN BNB DENGAN LOOPING--- */
// ganti $halaman_autofaucet_bnb dan $autofaucet_bnb_verify sesuai data jika mengganti coin
while(true){ //fungsi looping
	// masuk ke halaman autofaucetnya
	// contoh coin bnb (https://faucet-coinsum.online/auto/currency/bnb)
	$halaman_autofaucet_bnb = get("https://faucet-coinsum.online/auto/currency/bnb");
	$faucet_timer = explode(',',explode('let timer = ',$halaman_autofaucet_bnb)[1])[0];
	// print $faucet_timer; //untuk melihat waktu dalam hitungan detik (sesuai hasil explode)
	
	if($faucet_timer){ // jika timer ada
		// di sini kita stay dengan timer yang di tentukan
		// dalam detik
		for($i = $faucet_timer; $i >= 0; $i--) {
			echo "Waktu tersisa: " . $i . " detik...\r";
			sleep(1);
		}
		// jika menit maka bisa di ubah ke detik terlebih dahulu
	}
	
	$token = explode('">',explode('<input type="hidden" name="token" value="',$halaman_autofaucet_bnb)[1])[0];
	// cek data post terlebih dahulu untuk mengetahui data apa saja yang di butuhkan, di sini data postnya hanya membutuhkan token
	
	// contoh data post token=vZL18UoeMSkV4ARdJf5b
	// maka kita ganti setelah kalimat token= menjadi variable $token (yang sudah di explode sebelumnya)
	// karena token yang akan di butuhkan merupakan data random yang berada di repon sebelumnya
	
	$data_post = "token=".$token;
	
	// karena membutuhkan data untuk mendapatkan result selanjutnya/ bisa kita lihat di metode yang akan di gunakan selanjutnya menggunakan aplikasi sniff (httpcanary)
	// biasanya untuk claim dengan request data maka kita menggunakan fungsi post
	$autofaucet_bnb_verify = post("https://faucet-coinsum.online/auto/verify/bnb", $data_post);
	
	// hasilnya print $autofaucet_bnb_verify;
	// menentukan hasil jika sukses, pastikan explode lebih spesifik
	// karena sukses dan failed claim explode hampir sama
	// maka di sini saya mengambil langkah dengan explode html: '0.
	// karena jika failde explodenya html: ' tnapa 0. (hasil reward)
	$sukses_claim = explode("account!'",explode("html: '0.",$autofaucet_bnb_verify)[1])[0];
	
	//cek jika sukses
	if($sukses_claim){ // jika claim sukses ada
		// print 0. adalah pengganti dari data yang kita explode karena 0. sebelumnya merupakan acuan explode
		// $sukses_claim akan menghasilkan tampilan hasil reward yang sudah kita explode sebelumnya
		// "\n" untuk membuat baris baru ke bawah(enter)
		// ; adalah penutup
		print "0.".$sukses_claim."\n";
	}
	// menentukan jika claim tidak sukses
	// menggunakan else jika sebelumnya menggunakan if
	else {
		print "Claim tidak sukses\n";
	}
	
	//atau bisa menggunakan script di bawah ini
	// if(!$sukses_claim){ // jika claim sukses tidak ada
	//	print "Claim tidak sukses\n"
	// }
	//kembali ke awal dari looping
}
