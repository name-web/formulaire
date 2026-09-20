@if($errors->any())
    <div class="error-box" role="alert">
        <div class="error-header">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ __('Erreur') }}</span>
        </div>
        @foreach($errors->all() as $error)
            <div class="error-msg">{{ $error }}</div>
        @endforeach
    </div>
@endif
