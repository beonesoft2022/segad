@extends('layouts.auth2')
@section('title', __('lang_v1.register'))

@section('content')

    <div class="login-form col-md-12 col-xs-12 right-col-content-register">

	<div class="box box-solid">

        <div class="box-body">
            <p class="form-header text-white">@lang('business.register_and_get_started_in_minutes')</p>
            {!! Form::open([
                'url' => route('business.postRegister'),
                'method' => 'post',
                'id' => 'business_register_form',
                'files' => true,
            ]) !!}
            @include('business.partials.register_form')
            {!! Form::hidden('package_id', $package_id) !!}
            {!! Form::submit(__('messages.submit'), ['class' => 'btn btn-success pull-right']) !!}
            {!! Form::close() !!}
        </div>
    </div>
	<div class="modal fade brands_modal" tabindex="-1" role="dialog"
	aria-labelledby="gridSystemModalLabel">
</div>
</div>
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#change_lang').change(function() {
                window.location = "{{ route('business.getRegister') }}?lang=" + $(this).val();
            });
        })


		$(document).ready(function(){
            $('.select2_register').select2();
            $("form#business_register_form").validate({
                errorPlacement: function(error, element) {
                    if(element.parent('.input-group').length) {
                        error.insertAfter(element.parent());
                    } else {
                        error.insertAfter(element);
                    }
                },
                rules: {
                    name: "required",
                    email: {
                        email: true,
                        remote: {
                            url: "/business/register/check-email",
                            type: "post",
                            data: {
                                email: function() {
                                    return $( "#email" ).val();
                                }
                            }
                        }
                    },
                    password: {
                        required: true,
                        minlength: 5
                    },

                    paid_via: {
                        required: function(element){
                                return $('#package_id').val() != '';
                            }
                    },
                    username: {
                        required: true,
                        minlength: 4,
                        remote: {
                            url: "/business/register/check-username",
                            type: "post",
                            data: {
                                username: function() {
                                    return $( "#username" ).val();
                                }
                            }
                        }
                    }
                },
                messages: {
                    name: LANG.specify_business_name,
                    password: {
                        minlength: LANG.password_min_length,
                    },

                    username: {
                        remote: LANG.invalid_username
                    },
                    email: {
                        remote: '{{ __("validation.unique", ["attribute" => __("business.email")]) }}'
                    }
                }
            });

            $("#business_logo").fileinput({'showUpload':false, 'showPreview':false, 'browseLabel': LANG.file_browse_label, 'removeLabel': LANG.remove});
        });



    </script>
@endsection
