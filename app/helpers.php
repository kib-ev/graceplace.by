<?php

use Illuminate\Support\Str;

const BOOKING_ADD_COMMENT = 'booking_comment';
const BOOKING_CANCEL_COMMENT = 'cancellation_reason';
const ADMIN_COMMENT_TYPE = 'admin';

const CANCELLATION_CUTOFF_HOURS = '12';

function is_admin(): bool
{
    $user = auth()->user();
    return $user ? $user->hasRole(['admin']) : false;
}

function short_day_name(\Carbon\Carbon $carbon, bool $uppercase = false): string
{
    $shortDayName = substr(\Illuminate\Support\Carbon::parse($carbon)->locale('ru')->shortDayName, 0, 4);
    return $uppercase ? mb_strtoupper($shortDayName) : $shortDayName;
}

function do_tag_linkable($text)
{
    if (!is_null($text) && str_contains($text, '#')) {
        $regexp = '/(#)([a-zA-Zа-яА-Я0-9]+)/u';

        // Получаем текущие параметры запроса
        $currentParams = request()->query();

        $text = preg_replace_callback($regexp, function ($matches) use ($currentParams) {
            // Обновляем параметры, добавляя новый tag
            $params = array_merge($currentParams, ['tag' => $matches[2]]);
            $queryString = http_build_query($params);
            $url = request()->url() . '?' . $queryString;

            return '<a style="color: #7c7c37;" href="' . $url . '">' . $matches[0] . '</a>';
        }, $text);

        $text = str_replace("\r\n", "<br>", $text);
    }
    return $text;
}

function string_to_color($string) {
    // Хешируем строку в md5, берём первые 6 символов как HEX цвет
    $hash = md5($string);
    return '#' . substr($hash, 0, 6);
}


// является ли переданное число четным
function even($var): bool
{
    return !($var & 1);
}

function qr_code($link) {
    $options = new \chillerlan\QRCode\QROptions(
        [
//            'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
//            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
            'version' => 6,
        ]
    );

    return (new \chillerlan\QRCode\QRCode($options))->render($link);
}

function direct_link($profileLink): ?string
{
    if(isset($profileLink)) {
        $parts = explode('/', trim($profileLink,'/'));
        if ($parts > 0) {
            return 'https://ig.me/m/' . end($parts);
        }
    }

    return null;
}

function num2str(float $inn, $stripkop = false) {
    $nol = 'ноль';
    $str[100]= array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот', 'восемьсот','девятьсот');
    $str[11] = array('','десять','одиннадцать','двенадцать','тринадцать', 'четырнадцать','пятнадцать','шестнадцать','семнадцать', 'восемнадцать','девятнадцать','двадцать');
    $str[10] = array('','десять','двадцать','тридцать','сорок','пятьдесят', 'шестьдесят','семьдесят','восемьдесят','девяносто');
    $sex = array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),// m
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять') // f
    );
    $forms = array(
        array('копейка', 'копейки', 'копеек', 1), // 10^-2
        array('рубль', 'рубля', 'рублей',  0), // 10^ 0
        array('тысяча', 'тысячи', 'тысяч', 1), // 10^ 3
        array('миллион', 'миллиона', 'миллионов',  0), // 10^ 6
        array('миллиард', 'миллиарда', 'миллиардов',  0), // 10^ 9
        array('триллион', 'триллиона', 'триллионов',  0), // 10^12
    );
    $out = $tmp = array();
    // Поехали!
    $tmp = explode('.', str_replace(',','.', $inn));
    $rub = number_format($tmp[ 0], 0,'','-');
    if ($rub== 0) $out[] = $nol;
    // нормализация копеек
    $kop = isset($tmp[1]) ? substr(str_pad($tmp[1], 2, '0', STR_PAD_RIGHT), 0,2) : '00';
    $segments = explode('-', $rub);
    $offset = sizeof($segments);
    if ((int)$rub== 0) { // если 0 рублей
        $o[] = $nol;
        $o[] = morph( 0, $forms[1][ 0],$forms[1][1],$forms[1][2]);
    }
    else {
        foreach ($segments as $k=>$lev) {
            $sexi= (int) $forms[$offset][3]; // определяем род
            $ri = (int) $lev; // текущий сегмент
            if ($ri== 0 && $offset>1) {// если сегмент==0 & не последний уровень(там Units)
                $offset--;
                continue;
            }
            // нормализация
            $ri = str_pad($ri, 3, '0', STR_PAD_LEFT);
            // получаем циферки для анализа
            $r1 = (int)substr($ri,0,1); //первая цифра
            $r2 = (int)substr($ri,1,1); //вторая
            $r3 = (int)substr($ri,2,1); //третья
            $r22= (int)$r2.$r3; //вторая и третья
            // разгребаем порядки
            if ($ri>99) $o[] = $str[100][$r1]; // Сотни
            if ($r22>20) {// >20
                $o[] = $str[10][$r2];
                $o[] = $sex[ $sexi ][$r3];
            }
            else { // <=20
                if ($r22>9) $o[] = $str[11][$r22-9]; // 10-20
                elseif($r22> 0) $o[] = $sex[ $sexi ][$r3]; // 1-9
            }
            // Рубли
            $o[] = morph($ri, $forms[$offset][ 0],$forms[$offset][1],$forms[$offset][2]);
            $offset--;
        }
    }
    // Копейки
    if (!$stripkop) {
        $o[] = $kop;
        $o[] = morph($kop,$forms[0][0],$forms[0][1],$forms[0][2]);
    }
    return preg_replace("/\s{2,}/",' ',implode(' ',$o));
}

function morph($n, $f1, $f2, $f5) {
    $n = abs($n) % 100;
    $n1= $n % 10;
    if ($n>10 && $n<20)	return $f5;
    if ($n1>1 && $n1<5)	return $f2;
    if ($n1==1)		return $f1;
    return $f5;
}

function user_email_from_phone_number($phoneNumber): string
{
    return Str::replace(['+', ' ', '-', '(', ')'], '', $phoneNumber). '@graceplace.by';
}
