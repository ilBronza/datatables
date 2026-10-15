@foreach($field->getFieldOperations() as $fieldOperation)
    @php($label = $fieldOperation->getLabel())
    @if($link = $fieldOperation->getRenderedLink())
        @if($fieldOperation->getTarget() === 'iframe')<span uk-lightbox>@endif
        <a class='ibfieldoperation{{ $fieldOperation->getType() }} {{ $fieldOperation->getName() }}' href='{{ $link }}' title='{{ $fieldOperation->getTooltip() }}' uk-tooltip='@json(["title" => e($fieldOperation->getTooltip())])'
            @if($target = $fieldOperation->getTarget())
                @if($target === 'iframe') data-type='iframe'
                @else target='{{ $target }}' @if($target === '_blank') rel='noopener' @endif
                @endif
            @endif
            @if($fieldOperation->getType() === 'delete')
                data-type='DELETE' data-returnconfirm='{{ __('datatables::messages.areYouSureToDeleteThisObject') }}' data-send-ajax-payload='false' aria-label='{{ __('datatables::fields.mySelfDelete') }}'
            @endif
            @if(filled($label)) style='display: inline-block; padding: 2px 4px;'
            @elseif(! $fieldOperation->getFaIcon()) uk-icon='{{ $fieldOperation->getIcon() }}'
            @endif>
            @if(filled($label)){{ $label }}
            @elseif($faIcon = $fieldOperation->getFaIcon())<i class='fa-solid fa-{{ $faIcon }}' aria-hidden='true'></i>
            @endif
        </a>
        @if($fieldOperation->getTarget() === 'iframe')</span>@endif
    @else
        <span class='ibfieldoperation{{ $fieldOperation->getType() }} {{ $fieldOperation->getName() }}' title='{{ $fieldOperation->getTooltip() }}' uk-tooltip='@json(["title" => e($fieldOperation->getTooltip())])'
            @if($link = $fieldOperation->getLink()) data-substitutelink='{{ $link }}' @endif
            @if(! filled($label) && ! $fieldOperation->getFaIcon()) uk-icon='{{ $fieldOperation->getIcon() }}'
            @endif>
            @if(filled($label)){{ $label }}
            @elseif($faIcon = $fieldOperation->getFaIcon())<i class='fa-solid fa-{{ $faIcon }}' aria-hidden='true'></i>
            @endif
        </span>
    @endif
@endforeach
