<div class="pos-tab-content">
     <div class="row">
		<div class="col-sm-4">
            <div class="form-group">
                @php
                    $purchase_prefix = '';
                   /*  if(!empty($business->ref_no_prefixes['purchase'])){
                        $purchase_prefix = $business->ref_no_prefixes['purchase'];
                    } */
                @endphp
                {!! Form::label('', __('lang_v1.setting_einv_Username') . ':') !!}
                {!! Form::text('', $purchase_prefix, ['class' => 'form-control']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                @php
                    $purchase_return = '';

                @endphp
                {!! Form::label('', __('lang_v1.setting_einv_Password') . ':') !!}
                {!! Form::text('', $purchase_return, ['class' => 'form-control']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                @php
                    $purchase_order_prefix = '';
                @endphp
                {!! Form::label('', __('lang_v1.setting_einv_key') . ':') !!}
                {!! Form::text('', $purchase_order_prefix, ['class' => 'form-control']); !!}
            </div>
        </div>
    </div>
</div>
