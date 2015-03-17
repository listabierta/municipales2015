<?php
namespace Listabierta\Bundle\MunicipalesBundle\Lib\tractis;

use Listabierta\Bundle\MunicipalesBundle\Lib\tractis\TrustedTimestamps;

class SymfonyTractisApi
{
	const RFC_CONTRACT = 'https://api.tractis.com/rfc3161tsa';
	
	private static $tsa_cert_chain_file = NULL;
	private static $api_identifier      = NULL;
	private static $api_secret          = NULL;
	
	public function __construct($api_identifier = NULL, $api_secret = NULL, $tsa_cert_chain_file = NULL)
	{
		self::$tsa_cert_chain_file = $tsa_cert_chain_file;
		self::$api_identifier      = $api_identifier;
		self::$api_secret          = $api_secret;
	}
	
	public static function sign($data = NULL)
	{
		$data_hashed = sha1($data);
		
		$requestfile_path = TrustedTimestamps::createRequestfile($data_hashed);
		
		/*
		 Array
		 (
		 [response_string] => Shitload of text (base64-encoded Timestamp-Response of the TSA)
		 [response_time] => 1299098823
		 )
		 */
		$response = TrustedTimestamps::signRequestfile($requestfile_path, self::RFC_CONTRACT, self::$api_identifier, self::$api_secret);
		//print_r($response);

		return $response;
	}
	
	/**
	 * Validate content for a response
	 * 
	 * @param string $raw_data The raw data to validate
	 * @param string $response_string base64-encoded Timestamp-Response of the TSA
	 * @param string $response_time Timestamp-Response of the TSA
	 * 
	 * @author Ángel Guzmán Maeso <shakaran@gmail.com>
	 * 
	 * @return boolean
	 */
	public static function validate($raw_data = NULL, $response_string = NULL, $response_time = NULL)
	{
		$data_hashed = sha1($raw_data);
		
		return TrustedTimestamps::validate($data_hashed, 
										   $response_string, 
										   $response_time, 
										   self::$tsa_cert_chain_file);
		
	}
	
	/**
	 * Get the timestamp from a response (answer) in base64-encoded
	 * 
	 * @param string $response_string base64-encoded Timestamp-Response of the TSA
	 * 
	 * @author Ángel Guzmán Maeso <shakaran@gmail.com>
	 * 
	 * @return integer
	 */
	public static function getTimestamp($response_string = NULL)
	{
		return TrustedTimestamps::getTimestampFromAnswer($response_string); 
	}
}