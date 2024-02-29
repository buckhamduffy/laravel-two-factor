@extends('two-factor::layout')

@section('content')
    <form action="{{ route('two-factor-confirm.store') }}" method="post" class="form-horizontal">

        <div class="my-3 text-center">
            @if($type === 'authenticator')
                <p>Please open your authenticator app to get the TOTP code.</p>
            @elseif($type === 'sms')
                <p>Please check your SMS for the code.</p>
            @elseif($type === 'email')
                <p>Please check your Email for the code.</p>
            @endif
        </div>

        <input type="hidden" value="{{ $type }}" name="type">

        <fieldset>
            <div class="form-group{{ $errors->has('code') ? ' has-error' : '' }}">
                <div class="col-lg-12">
                    <input type="number" name="code" value="{{ old('code') }}" class="form-control"
                           placeholder="Code">
                    @if ($errors->has('code'))
                        <span class="help-block">
										<strong>{{ $errors->first('code') }}</strong>
									</span>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-12">
                    <button type="submit" class="btn btn-dark btn-block">Submit</button>
                </div>
            </div>
            {{ csrf_field() }}
            <hr>
        </fieldset>
    </form>

    <div class="form-group d-flex flex-column" style="gap: 5px;">
        @if($other_types['authenticator'])
            <div class="col-12">
                <a href="{{ route('two-factor-confirm', ['type' => 'authenticator']) }}"
                   class="btn btn-dark btn-block">
                    Use Authenticator
                </a>
            </div>
        @endif

        @if($other_types['sms'])
            <div class="col-12">
                <a href="{{ route('two-factor-confirm', ['type' => 'sms']) }}"
                   class="btn btn-dark btn-block">
                    Use SMS
                </a>
            </div>
        @endif

        @if($other_types['email'])
            <div class="col-12">
                <a href="{{ route('two-factor-confirm', ['type' => 'email']) }}"
                   class="btn btn-dark btn-block">
                    Use Email
                </a>
            </div>
        @endif

        @if($type === 'email' || $type === 'sms')
            <div class="col-12">
                <form action="{{ route('two-factor-confirm.resend') }}" method="post">
                    <input type="hidden" value="{{ $type }}" name="type">

                    {!! csrf_field() !!}

                    <button type="submit" class="btn btn-dark btn-block">
                        @if($type === 'email')
                            Resend Email
                        @elseif($type === 'sms')
                            Resend SMS
                        @endif
                    </button>
                </form>
            </div>
        @endif

        @if($other_types['recovery_codes'])
            <div class="col-12">
                <a href="{{ route('two-factor-recovery') }}"
                   class="btn btn-dark btn-block">
                    Use Recovery Code
                </a>
            </div>
        @endif
    </div>
@endsection
