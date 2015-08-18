<?php

class RC4Crypt {

	/**
	 * Encrypt the data.
	 * @param string private key.
	 * @param string data to be encrypted.
	 * @return string encrypted string.
	 */
	function encrypt ($pwd, $data)
	{
		$key[] = '';
		$box[] = '';

		$pwd_length = strlen($pwd);
		$data_length = strlen($data);

		for ($i = 0; $i < 256; $i++)
		{
			$key[$i] = ord($pwd[$i % $pwd_length]);
			$box[$i] = $i;
		}

		for ($j = $i = 0; $i < 256; $i++)
		{
			$j = ($j + $box[$i] + $key[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		
		$cipher = '';

		for ($a = $j = $i = 0; $i < $data_length; $i++)
		{
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;

			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;

			$k = $box[(($box[$a] + $box[$j]) % 256)];
			$cipher .= chr(ord($data[$i]) ^ $k);

		}

		return ($cipher);

	}

	/**
	 * Decrypt the data.
	 * @param string private key.
	 * @param string cipher text (encrypted text).
	 * @return string plain text. 
	 */
	function decrypt ($pwd, $data)
	{		
		return RC4Crypt::encrypt($pwd, ($data));
	}
}

?>