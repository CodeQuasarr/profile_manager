@extends('mails.layout')

@section('title', 'Accepter l\'invitation à rejoindre l\'équipe')

@section('content')
    <div class="card-body d-flex flex-wrap align-items-center justify-content-arround bg-black">
        <div class="col-md-8 col-lg-7 text-white p-3">
            <div class="bar mb-3"></div>
            <h1 class="fs-3">{{ $club }} n'attend que vous sur</h1>
            <h2 class="text-accent">Basket Fusion</h2>
            <p class="mt-5">Ton coach, {{ $name }}, souhaite t'ajouter à son équipe en ligne sur la plateforme de
                perfectionnement.</p>

            <a href="{{ $url }}" target="_blank" class="btn bg-accent text-white btn-lg my-3">Accepter l'invitation</a>
        </div>
        <div class="col-md-4 col-lg-5 ">
            <img
                src="https://cdn.pixabay.com/photo/2021/07/23/19/51/basketball-6488065_1280.png"
                class="img-fluid"
                alt=""
            >
        </div>
    </div>
@endsection

@section('custom-styles')
    <style>
        .bar {
            width: 75px;
            height: 5px;
            background-color: #b94a23;
        }

        .text-accent {
            color: #b94a23;
        }

        .bg-accent {
            background-color: #b94a23;
        }
    </style>
@endsection
