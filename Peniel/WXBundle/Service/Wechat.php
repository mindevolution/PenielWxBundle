<?php

/*
 * Copyright 2018 Zhili He
 * zhilihe.com
 */

namespace Peniel\WXBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use Peniel\WXBundle\Exception\WXException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Peniel\WXBundle\Entity\AccessToken;

/**
 * Description of Wechat
 *
 * @author Zhili He <zhili850702@gmail.com>
 */
class Wechat {

	/**
	 * 微信的app id
	 * @var type 
	 */
	private $wxAppId;
	private $wxSecret;

	/**
	 *
	 * @var ContainerInterface 
	 */
	private $container;

	public function __construct(ContainerInterface $container, $wxAppId, $wxSecret) {
		$this->container = $container;
		$this->wxAppId = $wxAppId;
		$this->wxSecret = $wxSecret;
	}

	private function getContainer() {
		return $this->container;
	}

	private function getDoctrine() {
		return $this->getContainer()->get('doctrine');
	}

	private function getWxAppId() {
		return $this->wxAppId;
	}

	private function getWxSecret() {
		return $this->wxSecret;
	}

	private function getAccessTokenRepository() {
		return $this->getDoctrine()->getRepository(AccessToken::class);
	}

	/**
	 * 首先检查数据库里面的accessToken是否已经过期，如果没有过期，直接返回
	 * 如果已经过期，重新请求accessToken
	 * 同时更新数据库的accessToken
	 * 
	 * @return mix
	 */
	public function getAccessToken() {
		$accessTokenRepository = $this->getAccessTokenRepository();
		$accessToken = $accessTokenRepository->find(1);
		// 如果数据库里面没有保存，重新请求后保存
		if(!$accessToken) {
			$accessToken = $this->createAccessToken();
		} elseif ($this->isAccessTokenExpires($accessToken)) {
			// 如果已经过期了,重新请求同时更新
			$accessToken = $this->renewAccessToken($accessToken);
		}
		return $accessToken->getAccessToken();
	}

	private function createAccessToken() {
		$entityManager = $this->getDoctrine()->getManager();
		$accessTokenObject = $this->requestAccessToken();
		$token = $this->fetchAccessTokenFromObje($accessTokenObject);
		$expiresIn = $this->fetchAccessTokenExpiresInFromObje($accessTokenObject);
		$accessToken = new AccessToken;
		$accessToken->setAccessToken($token);
		$accessToken->setExpiresIn($expiresIn);
		$accessToken->setCreatedTime(time());
		$entityManager->persist($accessToken);
		$entityManager->flush();

		return $accessToken;
	}

	private function renewAccessToken(AccessToken $accessToken) {
		$entityManager = $this->getDoctrine()->getManager();

		$accessTokenObject = $this->requestAccessToken();
		$token = $this->fetchAccessTokenFromObje($accessTokenObject);
		$expiresIn = $this->fetchAccessTokenExpiresInFromObje($accessTokenObject);

		$accessToken->setAccessToken($token);
		$accessToken->setExpiresIn($expiresIn);
		$accessToken->setCreatedTime(time());

		$entityManager->persist($accessToken);
		$entityManager->flush();

		return $accessToken;
	}

	/**
	 * 从微信返回的token stdObject里面提取token
	 * @param type $accessTokenObject
	 * @return type
	 */
	private function fetchAccessTokenFromObje(\stdClass $accessTokenObject) {
		return $accessTokenObject->access_token;
	}

	/**
	 * 从微信返回的token stdObject里面提取expiresIn
	 * @param \stdClass $accessTokenObject
	 * @return type
	 */
	private function fetchAccessTokenExpiresInFromObje(\stdClass $accessTokenObject) {
		return $accessTokenObject->expires_in;
	}

	private function requestAccessToken() {
		$api = sprintf('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
				$this->getWxAppId(), $this->getWxSecret());
		$client = $this->getGuzzleHttpClient();
		$res = $client->request('GET', $api);
		$data = json_decode($res->getBody());
		
		if (isset($data->errcode)) {
			throw new WXException('Get accessToken exception: ' . $data->errmsg);
		}
		if (!isset($data->errcode) && $data->access_token) {
			return $data;
		}
		
		return false;
	}

	/**
	 * 获取素材列表
	 * @return type
	 */
	public function getMaterial() {
		$accessToken = $this->getAccessToken();
		$api = sprintf('https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=%s', $accessToken);
		$method = 'POST';

		$client = $this->getGuzzleHttpClient();
		$res = $client->request($method, $api);
		$data = json_decode($res->getBody());
		return $data;
	}

	/**
	 * 检查微信服务器的url配置
	 * @param Request $request
	 * @return type
	 */
	public function checkSignature(Request $request) {
		$signature = $request->get("signature");
		$timestamp = $request->get("timestamp");
		$nonce = $request->get("nonce");

		$token = 'christiantimes';
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = sha1(implode($tmpArr));
		return $tmpStr === $signature;
	}

	/**
	 * 获取验证服务器url的时候的echostr
	 * @param Request $request
	 * @return type
	 */
	public function getEchoStr(Request $request) {
		return $request->get("echostr");;
	}

	private function getGuzzleHttpClient() {
		return new Client();
	}

	/**
	 * 检查accessToken是否已经过期
	 * 如果没有过期，返回false
	 * 如果过期了，返回true
	 * @param AccessToken $accessToken
	 * @return boolean
	 */
	private function isAccessTokenExpires(AccessToken $accessToken) {
		$expires = true;
		$expiresTime = $accessToken->getCreatedTime() + $accessToken->getExpiresIn();
		if ($expiresTime > time()) {
			$expires = false;
		}

		return $expires;
	}
}
