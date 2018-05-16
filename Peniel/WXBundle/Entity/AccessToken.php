<?php

namespace Peniel\WXBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccessToken
 *
 * @ORM\Table(name="wx_access_token")
 * @ORM\Entity(repositoryClass="Peniel\WXBundle\Repository\AccessTokenRepository")
 */
class AccessToken
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="accessToken", type="string", length=255)
     */
    private $accessToken;

    /**
     * @var int
     *
     * @ORM\Column(name="expiresIn", type="integer")
     */
    private $expiresIn;

    /**
     * @var int
     *
     * @ORM\Column(name="createdTime", type="integer")
     */
    private $createdTime;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set accessToken
     *
     * @param string $accessToken
     *
     * @return AccessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set expiresIn
     *
     * @param integer $expiresIn
     *
     * @return AccessToken
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Get expiresIn
     *
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * Set createdTime
     *
     * @param integer $createdTime
     *
     * @return AccessToken
     */
    public function setCreatedTime($createdTime)
    {
        $this->createdTime = $createdTime;

        return $this;
    }

    /**
     * Get createdTime
     *
     * @return int
     */
    public function getCreatedTime()
    {
        return $this->createdTime;
    }
}

