<?php
declare(strict_types=1);

/**
 * Laravel API Response Builder
 *
 * @author    Mustafa Online
 * @copyright 2016-2021 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-response-builder
 */
return [

    'ok'                       => 'حسناً',
    'no_error_message'         => 'خطأ #:api_code',

    // Used by Exception Handler Helper (when used)
    'uncaught_exception'       => 'استثناء غير ممسك: :message',
    'http_exception'           => 'HTTP استثناء: :message',

    // HttpException handler (added in 6.4.0)
    // Error messages for HttpException caught w/o custom messages
    // https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    'http_400'                 => 'طلب غير صحيح',
    'http_401'                 => 'غير مصرح به',
    'http_402'                 => 'الدفع مطلوب',
    'http_403'                 => 'ممنوع',
    'http_404'                 => 'لا يوجد',
    'http_405'                 => 'الأسلوب غير مسموح به',
    'http_406'                 => 'غير مقبول',
    'http_407'                 => 'مطلوب مصادقة الوكيل',
    'http_408'                 => 'انتهاء مهلة الطلب',
    'http_409'                 => 'تضارب',
    'http_410'                 => 'انتهى',
    'http_411'                 => 'الطول مطلوب',
    'http_412'                 => 'فشل الشروط المسبقة',
    'http_413'                 => 'حمولة كبيرة جدا',
    'http_414'                 => 'URI طويل جداً',
    'http_415'                 => 'نوع الوسائط غير مدعوم',
    'http_416'                 => 'النطاق غير قابل للرضا',
    'http_417'                 => 'فشل التوقعات',
    'http_421'                 => 'طلب توجيه خاطئ',
    'http_422'                 => 'كيان غير قابل ل المعالجة',
    'http_423'                 => 'مؤمن',
    'http_424'                 => 'إعتمادية فاشلة',
    'http_425'                 => 'مبكر جداً',
    'http_426'                 => 'الترقية مطلوبة',
    'http_428'                 => 'شرط مسبق مطلوب',
    'http_429'                 => 'طلبات كثيرة جداً',
    'http_431'                 => 'حقول رأس الطلب كبيرة جداً',
    'http_451'                 => 'غير متوفر لأسباب قانونية',

    'http_500'                 => 'خطأ داخلي في الخادم',
    'http_501'                 => 'لم يتم تنفيذه',
    'http_502'                 => 'بوابة غير صالحة',
    'http_503'                 => 'الخدمة غير متوفرة',
    'http_504'                 => 'مهلة المنفذ',
    'http_505'                 => 'إصدار HTTP غير مدعوم',
    'http_506'                 => 'البديل أيضاً يتفاوض',
    'http_507'                 => 'تخزين غير كاف',
    'http_508'                 => 'تم الكشف عن حلقة تكرار',
    'http_509'                 => 'غير معين',
    'http_510'                 => 'غير موسعة',
    'http_511'                 => 'مطلوب مصادقة شبكة الاتصال',
];
