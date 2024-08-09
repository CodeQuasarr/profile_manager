@extends('mails.layout')

@section('title', 'Confirmation de votre compte')

@section('content')
    <div class="card-body">
        <h1 class="fs-6">Bonjour {{ $name }} ,</h1>
        <p>Nous avons le plaisir de vous informer que votre compte a été créé avec succès. Vous pouvez maintenant vous connecter et profiter de nos services.</p>

        <p>Si vous avez des questions ou besoin d'assistance, n'hésitez pas à nous contacter.</p>

        <p>Merci de votre confiance.</p>

        <p>Cordialement,</p>
        <p>L'équipe de support</p>
    </div>
@endsection

@section('custom-styles')

@endsection

