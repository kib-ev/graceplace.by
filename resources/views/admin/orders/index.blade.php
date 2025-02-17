@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Создание счета E-POST</h1>

            <hr>

            <div class="row">
                <div class="col-5">
                    <form  action="" method="POST">
{{--                        @method('post')--}}
{{--                        @csrf--}}

                        <div class="form-group mb-2">
                            <label for="">номер счета (строка)</label>
                            <input class="form-control" type="text" name="invoiceNumber" placeholder="invoiceNumber" value="{{ now()->getTimestamp() }}">
                        </div>

                        <div class="form-group mb-2">
                            <label for="">внутренний идентификатор плательщика в АИС ПКС - целое число</label>
                            <input class="form-control" type="text" name="payerId" placeholder="payerId" value="">
                        </div>

                        <div class="form-group mb-2">
                            <label for="">сумма счета - целое число ??? (строка)</label>
                            <input class="form-control" type="text" name="totalSum" placeholder="totalSum" value="10.00">
                        </div>

                        <div class="form-group mb-2">
                            <label for="">разрешение на редактирование суммы (0 – нет, 1 – да)</label>
                            <select class="form-control" name="canEditAmount" id="">
                                <option value="0">0 - нет</option>
                                <option value="1">1 - да</option>
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label for="">назначение платежа (строка)</label>
                            <input class="form-control" type="text" name="purposePayment" placeholder="purposePayment" value="TEST API">
                        </div>

                        <div class="form-group mb-2">
                            <label for="">статус счета, ID из справочника статусов счета (число)</label>
                            <select class="form-control" name="invoiceStatus" id="">
                                <option value="0">0 - нет</option>
                                <option value="1">1 - да</option>
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label for="">тип счета (1 – однократный, 2 – многократный)</label>
                            <select class="form-control" name="invoiceType" id="">
                                <option value="1">1 – создан</option>
                                <option value="2">2 – активен</option>
                                <option value="3">3 – оплачен</option>
                                <option value="4">4 – закрыт</option>
                                <option value="5">5 – частично оплачен</option>
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label for="">счет действителен до этой даты (timestamp)</label>
                            <input class="form-control" type="text" name="expaireDate" placeholder="expaireDate" value="{{ now()->addDays(30)->getTimestamp() }}">
                        </div>

                        <div class="form-group mb-2">
                            <label for="">адрес электронной почты</label>
                            <input class="form-control" type="text" name="email" placeholder="email" value="">
                        </div>

                        <div class="form-group mb-2">
                            <label for="">номер телефона</label>
                            <input class="form-control" type="text" name="phone" placeholder="phone" value="">
                        </div>

                        <div class="form-group mb-2">
                            <label for="">оплачиваемая услуга</label>
                            <input class="form-control" type="text" name="eposService" placeholder="eposService" value="Аренда">
                        </div>

                        <div class="form-group mb-2">
                            <label for="">идентификатор услуги Е-POS</label>
                            <input class="form-control" type="text" name="eposServiceId" placeholder="eposServiceId" value="">
                        </div>


                        <button type="submit">Отправить</button>

                    </form>
                </div>
            </div>

            {
            "invoiceNumber": 2131232142279,
            "payerId": 94,
            "totalSum": "13123.00",
            "canEditAmount": 0,
            "purposePayment": "purpose",
            "invoiceStatus": 2,
            "invoiceType": 1,
            "expaireDate": 2000453127995,
            "email": "zely1984@gmail.com",
            "phone": "+375(29)630-64-85",
            "eposService": "Mocked service (1)",
            "eposServiceId": 1
            }



        </div>
    </div>
@endsection

