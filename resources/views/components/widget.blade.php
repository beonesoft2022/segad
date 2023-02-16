
<head>
	<style>


.button-header-40 {
    width: 100%;
    font-size: 15px;
    height: 40px;
    font-family: 'droid';
    margin-bottom: 5px;


	background-color: #111827;
	border: 1px solid transparent;
	border-radius: .75rem;
	box-sizing: border-box;
	color: #FFFFFF;
	cursor: pointer;
	flex: 0 0 auto;
	font-weight: 600;
	line-height: 1.5rem;
	padding: .75rem 1.2rem;
	text-align: center;
	text-decoration: none #6B7280 solid;
	text-decoration-thickness: auto;
	transition-duration: .2s;
	transition-property: background-color,border-color,color,fill,stroke;
	transition-timing-function: cubic-bezier(.4, 0, 0.2, 1);
	user-select: none;
	-webkit-user-select: none;
	touch-action: manipulation;

  }

  .button-header-40:hover {
	background-color: #374151;
  }

  .button-header-40:focus {
	box-shadow: none;
	outline: 2px solid transparent;
	outline-offset: 2px;
  }

  @media (min-width: 768px) {
	.button-header-40 {
	  padding: .75rem 1.5rem;
	}
  }



	</style>
</head>



<button href="#" class="button-header-40">{{ $title ?? '' }}</button>

<div class="box {{$class ?? 'box-solid'}}" @if(!empty($id)) id="{{$id}}" @endif>
    @if(empty($header))
        @if(!empty($title) || !empty($tool))
        <div class="box-header">
            {!!$icon ?? '' !!}

            {!!$tool ?? ''!!}
        </div>
        @endif
    @else
        <div class="box-header">
            {!! $header !!}
        </div>
    @endif

    <div class="box-body">
        {{$slot}}
    </div>
    <!-- /.box-body -->
</div>
