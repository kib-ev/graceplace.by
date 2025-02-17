@extends('public.layouts.main')


@section('content')
    <section id="workspace" class="py-5">
        <div class="container">
            <div class="row">
                <!-- Изображение помещения -->
                <div class="col-md-6">
                    <img style="width: 100%;" class="img-fluid" src="{{ $place->image_path ?? 'https://placehold.co/200x125' }}" alt="{{ $place->name }}">
                </div>
                <!-- Описание помещения -->
                <div class="col-md-6">
                    <h2>{{ $place->name }}</h2>

                    <p>
                        Для мастеров, которые ищут идеальное место для работы, мы создали пространство, которое сочетает в себе комфорт и функциональность.
                    </p>
                    <p>
                        Это идеальное решение для мастеров, которые ценят удобство, качество и стильное рабочее пространство. Приходите и работайте в лучших условиях!
                    </p>
{{--                    <a href="#" class="btn btn-primary">Забронировать место</a>--}}
                </div>
            </div>
        </div>
    </section>
@endsection
