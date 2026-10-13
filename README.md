# Symfony DaData

## Описание

Symfony DaDataBundle предназначен для работы с API сервиса [ДаДата](https://dadata.ru). Это форк [Velhron/dadata-bundle](https://github.com/Velhron/dadata-bundle) для PHP 8.5 и Symfony 8.

## Установка

Для работы нужны PHP 8.5.x и Symfony 8.x.

Пока пакет не опубликован на Packagist, добавьте репозиторий форка в проект и установите ветку `master`:

```bash
composer config repositories.astro-masters-dadata vcs https://github.com/astro-masters/dadata-bundle
composer require astro-masters/dadata-bundle:dev-master
```

Если в проекте установлен `velhron/dadata-bundle`, сначала выполните `composer remove velhron/dadata-bundle`: оба пакета используют одни PHP-классы.

После публикации тега `v1.0.0` на Packagist запись `repositories` в проекте будет не нужна. Для новой установки достаточно:

```bash
composer require astro-masters/dadata-bundle:^1.0
```

Если бандл не зарегистрирован автоматически, добавьте его в `config/bundles.php` приложения:

```php
<?php

return [
    Velhron\DadataBundle\VelhronDadataBundle::class => ['all' => true],
];
```

## Конфигурация

Создайте файл конфигурации `config/packages/velhron_dadata.yaml`:

### Пример

```yaml
velhron_dadata:
    token: '%env(DADATA_TOKEN)%'
    secret: '%env(DADATA_SECRET)%'
    # Если у вас инфраструктура состоит из n-сервисов, которые обращаются в dadata, то для контроля запросов в dadata
    # в одной точке, Вам возможно потребуется прокси-кеш. Для замены оригинальных url от dadata на Ваш прокси, можете
    # заполнить следующие необязательные параметры
    #base_general_url: 'https://proxy_dadata.ru/proxy/v2'
    #base_cleaner_url: 'https://cleaner.proxy_dadata.ru/proxy/v1/clean'
    #base_suggestions_url: 'https://suggestions.proxy_dadata.ru/suggestions/proxy/4_1/rs'
```

## Использование

Прежде всего, необходимо подключить для работы нужный вам сервис. Например:

```php
<?php

// ...

use Velhron\DadataBundle\Service\DadataSuggest;

class BaseController extends AbstractController
{
    private $dadataSuggest;

    public function __construct(DadataSuggest $dadataSuggest)
    {
        $this->dadataSuggest = $dadataSuggest;
    }
}
```

Всего сервисов - 5, а именно:
1. `DadataSuggest` - [подсказки](https://dadata.ru/api/suggest/)
2. `DadataClean` - [стандартизация](https://dadata.ru/api/clean/)
3. `DadataGeolocate` - [обратное геокодирование](https://dadata.ru/api/geolocate/)
4. `DadataIplocate` - [город по IP-адресу](https://dadata.ru/api/iplocate/)
5. `DadataGeneral` - остальные методы

Все доступные методы можно посмотреть в самих классах.

Дополнительные параметры обычно передаются вторым параметром в виде ассоциативного массива. 
Все параметры аналогичны тем, что указаны на сайте ДаДаты.

### [API подсказок](https://dadata.ru/api/suggest/)

Например, подсказки по адресам:

```php
$response = $dadataSuggest->suggestAddress('москва хабар', ['count' => 10]);
$address = $response[0]->value;
```

Подсказки по организациям:

```php
$response = $dadataSuggest->suggestParty('сбербанк', ['count' => 2]);
$inn = $response[0]->inn;
```

### [API стандартизации](https://dadata.ru/api/clean/)

Например, стандартизация ФИО:

```php
$response = $dadataClean->cleanName('Срегей владимерович иванов');
$name = $response->result;
```

### [Обратное геокодирование](https://dadata.ru/api/geolocate/)

Например, адрес по координатам:

```php
$response = $dadataGeolocate->geolocateAddress(55.878, 37.653);
$address = $response[0]->value;
```

### [Город по IP-адресу](https://dadata.ru/api/iplocate/)

Получение города по IP адресу:

```php
$response = $dadataIplocate->iplocateAddress('46.226.227.20');
$city = $response->value;
```

### [Адрес по коду КЛАДР или ФИАС](https://dadata.ru/api/find-address/)

Получение адреса по коду КЛАДР:

```php
$response = $dadataSuggest->findAddress('77000000000268400');
$address = $response[0]->value;
```

### [Поиск аффилированных компаний](https://dadata.ru/api/find-affiliated/)

```php
$response = $dadataSuggest->findAffiliatedParty('7736207543');
$value = $response[0]->value;
```

## Лицензия

[MIT License](https://opensource.org/licenses/mit-license) © Velhron

## Публикация

Перед первым выпуском проверьте тесты на PHP 8.5 и Symfony 8.0/8.1, создайте тег `v1.0.0` в этом форке и отправьте его в `origin`. Затем зарегистрируйте `https://github.com/astro-masters/dadata-bundle` на [Packagist](https://packagist.org/) и настройте обновление пакета из GitHub. PHP namespace `Velhron\DadataBundle` сохранён для совместимости с существующим кодом.
