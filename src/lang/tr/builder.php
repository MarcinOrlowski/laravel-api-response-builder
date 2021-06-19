<?php
declare(strict_types=1);

/**
 * Laravel API Response Builder
 *
 * @author    Muzaffer Ali AKYIL <m.akyil (#) qt (.) net (.) tr>
 * @copyright 2016-2021 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-response-builder
 */
return [

    'ok'                       => 'Tamam',
    'no_error_message'         => 'Hata #:api_code',

    // Used by Exception Handler Helper (when used)
    'uncaught_exception'       => 'Yakalanmamış Hata: :message',
    'http_exception'           => 'HTTP Hatası: :message',

    // HttpException handler (added in 6.4.0)
    // Error messages for HttpException caught w/o custom messages
    // https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    // Turkish Translated from https://tr.wikipedia.org/wiki/HTTP_durum_kodlar%C4%B1
    'http_400'                 => 'Kötü İstek',
    'http_401'                 => 'Yetkisiz',
    'http_402'                 => 'Ödeme Gerekli',
    'http_403'                 => 'Yasaklandı',
    'http_404'                 => 'Sayfa Bulunamadı',
    'http_405'                 => 'İzin Verilmeyen Metod',
    'http_406'                 => 'Kabul Edilemez',
    'http_407'                 => 'Proxy Sunucusunda Giriş Yapmak Gerekli',
    'http_408'                 => 'İstek Zaman Aşamına Uğradı',
    'http_409'                 => 'Çakışma',
    'http_410'                 => 'Bak',
    'http_411'                 => 'Uzunluk Gerekli',
    'http_412'                 => 'Ön Koşul Başarısız',
    'http_413'                 => 'Girilen Veri Çok Fazla',
    'http_414'                 => 'URI Çok Uzun',
    'http_415'                 => 'Desteklenmeyen Medya Tipi',
    'http_416'                 => 'İstenen Aralık Kabul Edilemez',
    'http_417'                 => 'Beklenti Başarısız',
    'http_421'                 => 'Yanlış Yönlendirilmiş Talep',
    'http_422'                 => 'İşlenemeyen Varlık',
    'http_423'                 => 'Kilitli',
    'http_424'                 => 'Başarısız Bağımlılık',
    'http_425'                 => 'Çok Erken',
    'http_426'                 => 'Güncelleme Gerekli',
    'http_428'                 => 'Ön Koşul Gerekli',
    'http_429'                 => 'Çok Fazla İstek Gönderildi',
    'http_431'                 => 'İstek Başlık Alanları Çok Büyük',
    'http_451'                 => 'Yasal Nedenlerle Gösterilemiyor',

    'http_500'                 => 'Dahili Sunucu Hatası',
    'http_501'                 => 'Uygulanamadı',
    'http_502'                 => 'Hatalı Ağ Geçidi',
    'http_503'                 => 'Hizmet Kullanılamıyor',
    'http_504'                 => 'Ağ Geçidi Zaman Aşımı',
    'http_505'                 => 'HTTP Versiyonu Desteklenmiyor',
    'http_506'                 => 'Varyant Ayrıca Müzakere Ediyor',
    'http_507'                 => 'Yetersiz Depolama Alanı',
    'http_508'                 => 'Döngü Algılandı',
    'http_509'                 => 'Atanmamış',
    'http_510'                 => 'Uzatılmamış',
    'http_511'                 => 'Ağ Kimlik Doğrulaması Gerekli',
];

