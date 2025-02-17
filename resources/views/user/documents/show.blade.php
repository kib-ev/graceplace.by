<html>
<head>
    <title>Акт об оказании услуг {{ $appointment->id }} от {{ $appointment->end_at->format('d.m.Y') }}</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        body {
            font-family: Calibri, Helvetica, sans-serif;
        }

        p {
            padding: 0px;
        }

        .vas ul {
            padding: 0px 10px 0px 15px;
        }

        .vas li {
            list-style-type: circle;
        }

        h3 {
            padding: 0px 0px 0px 5px;
            font-size: 100%;
        }

        h1 {
            padding: 0px 0px 0px 5px;
            font-size: 16px;
        }

        li {
            list-style-type: none;
            padding-bottom: 5px;
            padding: 6px 0px 0px 5px;
        }

        .main {
            font-size: 14px;
        }

        .top {
            width: 100%;
        }

        .left {
            float: left;
        }

        .right {
            text-align: right;
        }

        .clear {
            clear: both;
        }

        .type {
            border: 1px solid #333;
            padding: 10px;
            font-weight: bold;
            font-size: 12px;
        }

        .list {
            font-size: 12px;
            padding: 6px 15px 0px 5px;
        }

        .main input {
            background-color: #CCFFCC;
        }

        .text14 {
            width: 720px;
        }

        .text14 strong {
            font-size: 11px;
        }

        .link {
            font-size: 12px;
        }

        .link a {
            text-decoration: none;
            color: #006400;
        }

        .link_u {
            font-size: 12px;
        }

        .link_u a {
            color: #006400;
        }

        table td {
            border: #000000 1px solid;
            padding: 2px 5px;
        }

        span.tax {
            font-size: 12px;
            font-style: italic;
        }

        p.podstroka {
            font-size: 11px;

        }

        p.company {
            font-size: 13px;
            letter-spacing: normal;
            text-align: center;
            font-weight: normal;
            padding: 0px;
            margin: 0px 0px 3px;
        }

        .hide {
            display: none;
        }
    </style>
</head>
<body>

<style>
    #Document p {
        margin-bottom: 0px;
        margin-top: 0px;
    }
</style>

<div id="Document" class="text14">
    <div class="top">

        <p>ООО "ЭкоСпеции"</p>
        <p>УНП: 192683473</p>
        <p>Р/сч: BY47 MTBK 3012 0001 0933 0007 2363 в ЗАО "МТБанк", код MTBKBY22</p>
        <p>Адрес: 220116, г. Минск, пр-т Газеты Правда, д. 20А, пом. 14, тел. +375(29)535-28-36</p>

    </div>

    <br> <br> <br> <br>

    <h1 class="clear" style="width:720px; text-align: center;">Акт № {{ $appointment->id }} от {{ $appointment->end_at->format('d.m.Y') }}</h1>

    <p>Заказчик: {{ $appointment->user->getFullName(true) }}</p>
    <p>Адрес: , тел.: {{ $appointment->user->phone }}</p>
    <p>Договор: Публичная оферта от 01.01.2025</p>

    <br>

    <table class="border" cellpadding="0" cellspacing="0" style="width: 100%;">

        <tbody>
        <tr>
            <td style="width: 26px;"><b>№ п/п</b></td>
            <td><b>Наименование</b></td>
            <td><b>Ед. изм.</b></td>
            <td><b>Кол-во</b></td>
            <td><b>Цена, руб. коп.</b></td>
            <td><b>Сумма, руб. коп.</b></td>
            <td><b>Ставка НДС, %</b></td>
            <td><b>Сумма НДС, руб. коп.</b></td>
            <td><b>Всего с НДС , руб. коп.</b></td>
        </tr>


        <tr>

            <td>1</td>
            <td>Услуги по сдаче в аренду рабочего места по публ. оферта от 01.01.2025</td>
            <td>шт</td>
            <td class="right">1</td>

            <td class="right">{{ $appointment->price }}</td>
            <td class="right">{{ $appointment->price }}</td>
            <td class="right">20%</td>
            <td class="right">{{ number_format($appointment->price * 0.2, 2, '.', '') }}</td>
            <td class="right">{{ $appointment->price }}</td>
        </tr>


        <tr>
            <td class="right" colspan="8"><b>Итого:</b></td>
            <td class="right"><b>{{ $appointment->price }}</b></td>
        </tr>
        <tr>
            <td class="right" colspan="8"><b>В том числе НДС:</b></td>
            <td class="right"><b>{{ number_format($appointment->price * 0.2, 2, '.', '') }}</b></td>
        </tr>
        <tr>
            <td class="right" colspan="8"><b>Всего с НДС:</b></td>
            <td class="right"><b>{{ $appointment->price }}</b></td>
        </tr>

        </tbody>
    </table>

    <br>

    <p>Всего оказано услуг 1, на сумму: {{ num2str($appointment->price) }}, <br>в т.ч. НДС: {{ num2str($appointment->price * 0.2) }}.</p>

    <br>
    <p>Вышеперечисленные услуги выполнены полностью и в срок. <br>Заказчик претензий по объему, качеству и срокам оказания услуг не имеет.</p>

    <br>

    <div class="footer">

    <br>

    <p>Исполнитель:
        <br>
        <br>
        директор ________________________ Киб Е.В.</p>

    @include('user.documents.includes.stamp')


    </div>
</div>

</body>
</html>
