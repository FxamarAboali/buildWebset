<table class="table">
    <thead>
    <tr>
        <td style="width:40px"></td>
        <td></td>
        <td><?php _e("Control"); ?></td>
        <td><?php _e("Enable"); ?></td>
    </tr>
    </thead>

    @foreach($customFieldNames as $customFieldKey=>$customField)
        <tr class="js-filter-custom-field-holder vertical-align-middle show-on-hover-root" data-field-custom-field-key="{{$customFieldKey}}">
            <td>
                <i data-title="<?php _e("Reorder filters"); ?>" data-toggle="tooltip" class="js-filter-custom-field-handle-field mdi mdi-cursor-move mdi-18px text-muted show-on-hover" style="cursor: pointer;"></i>
            </td>
            <td>
                {{ucfirst($customField->name)}}
            </td>

            <td>
                @php
                    $customFieldControlTypeOptionName = 'filtering_by_custom_fields_control_type_' . $customFieldKey;
                @endphp
                <select class="mw_option_field form-control" name="{{$customFieldControlTypeOptionName}}">
                    <option value="" disabled="disabled"><?php _e("Select control type"); ?></option>
                    <option value="checkbox" <?php if ('checkbox' == $customField->controlType): ?>selected="selected"<?php endif; ?>><?php _e("Checkbox"); ?></option>
                    <option value="radio" <?php if ('radio' == $customField->controlType): ?>selected="selected"<?php endif; ?>><?php _e("Radio button"); ?></option>
                    <option value="select" <?php if ('select' == $customField->controlType): ?>selected="selected"<?php endif; ?>><?php _e("Selectbox"); ?></option>
                    <option value="slider" <?php if ('slider' == $customField->controlType): ?>selected="selected"<?php endif; ?>><?php _e("Slider"); ?></option>
                    <option value="price_range" <?php if ('price_range' == $customField->controlType): ?>selected="selected"<?php endif; ?>><?php _e("Price Range"); ?></option>
                    <option value="square_checkbox" <?php if ('square_checkbox' == $customField->controlType): ?>selected="selected"<?php endif; ?>><?php _e("Square checkbox"); ?></option>
                    <option value="color" <?php if ('color' == $customField->controlType): ?>selected="selected"<?php endif; ?>><?php _e("Color"); ?></option>
                    <option value="date" <?php if ('date' == $customField->controlType): ?>selected="selected"<?php endif; ?>><?php _e("Date"); ?></option>
                    <option value="date_range" <?php if ('date_range' == $customField->controlType): ?>selected="selected"<?php endif; ?>><?php _e("Date Range"); ?></option>
                </select>
            </td>
            <td>
                @php
                    $customFieldOptionName = 'filtering_by_custom_fields_' . $customFieldKey;
                @endphp
                <div class="custom-control custom-switch pl-0">
                    <label class="d-inline-block mr-5" for="{{$customFieldOptionName}}"></label>
                    <input type="checkbox" <?php if ('1' == get_option($customFieldOptionName, $moduleId)): ?>checked="checked"<?php endif; ?> name="{{$customFieldOptionName}}" data-value-checked="1" data-value-unchecked="0" id="{{$customFieldOptionName}}" class="mw_option_field custom-control-input">
                    <label class="custom-control-label" for="{{$customFieldOptionName}}"></label>
                </div>
            </td>
        </tr>
    @endforeach
</table>