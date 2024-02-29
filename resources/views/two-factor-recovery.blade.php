@extends('two-factor::layout')

@section('content')
    <form action="{{ route('two-factor-recovery.store') }}" method="post" class="form-horizontal">
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
        </fieldset>
    </form>
@endsection
