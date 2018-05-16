<?php

namespace Peniel\WXBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Peniel\WXBundle\Service\Wechat;

class WXController extends Controller {

	public $service_peniel_wx_wechat = 'peniel_wx.wechat';

	public function indexAction() {
		// 获取微信的配置
		$wechatService = $this->get('peniel_wx.wechat');
		$materials = $wechatService->getMaterial();
		var_dump($materials);
		exit;
		return $this->render('@PenielWX/WX/index.html.twig', [
			]
		);
	}

	/**
	 * 
	 * @return Wechat
	 */
	public function getWechatService() {
		return $this->get($this->service_peniel_wx_wechat);
	}

	/**
	 * check signature
	 * @param Request $request
	 * @return boolean
	 */
	public function checkAction(Request $request) {
		$wechatService = $this->getWechatService();
		if ($wechatService->checkSignature($request)) {
			return new Response($wechatService->getEchoStr($request), 200);
		} else {
			return new JsonResponse(false, 200);
		}
	}

}
