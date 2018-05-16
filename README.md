# PenielWxBundle
Symfony3 wechat bundle, get wechat access_token, api...

## Install
1. copy files to src folder: src/peniel/PenielWxBundle
2. Register in file app/AppKernel.php::registerBundles
`
        $bundles = [
            // ...
            new Peniel\WXBundle\PenielWXBundle(),
        ];
`
3. Register route, update file app/routing.yml add
`
peniel_wx:
    resource: "@PenielWXBundle/Resources/config/routing.yml"
    prefix:   /wx
`
## Update database
`
php bin/console doctrine:schema:update --force
`
## Update config
update config file to set wechat appId and appSecret, file name: src/Peniel/WXBundle/Resource/config/services.yml

## test url
`
php bin/console server:run
`
http://localhost:8000/app_dev.php/wx/index
