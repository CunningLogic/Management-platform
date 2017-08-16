<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>{$LANGDATA.iuav_title}</title>

    <link rel="stylesheet" type="text/css" href="/public/css/externals/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="/public/css/common.css?v=17"/>
    <link rel="stylesheet" type="text/css" href="/public/css/active.css?v=17"/>
</head>
<body class="{$country}">
    <div class="page-container">
        {include "left_side.tpl" nav="active"}

        <div class="main-side">
            {include "top.tpl"}

            <div class="main-body" id="mainBody">
                <form class="form-horizontal active-form" id="activeForm">
                    <div class="form-section" data-section="device">
                        <p class="form-title">{$LANGDATA.iuav_active_info}</p>
                        <div class="device-groups" id="deviceGroups">
                            <script type="text/html" id="tpl_deviceGroup">
                                {literal}  
                                <div class="device-group" data-id="{{localId}}">


                                    {{if allowRemove}}
                                    <div class="form-group form-operation">
                                        <div class="col-sm-offset-4 col-sm-6">
                                            <a href="javascript:;" action-type="remove" class="button-remove">
                                                <span class="glyphicon glyphicon-remove"></span>
                                            </a>
                                        </div>
                                    </div>
                                    {{/if}}
                                    {/literal}
                                    <div class="form-group essential">
                                        <label class="col-sm-4 control-label">{$LANGDATA.iuav_model}</label>
                                        <div class="col-sm-6">
                                            <button type="button" class="btn btn-default active" name="type" value="mg-1">MG-1</button>
                                        </div>
                                    </div>
                                    <div class="form-group essential" input-check="true">
                                        <label class="col-sm-4 control-label">{$LANGDATA.iuav_enter_hardware}</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="hardware_id" />
                                            <p class="help-block">{$LANGDATA.iuav_connect_assistant}<a href="" data-toggle="modal" data-target="#viewModal" data-src="/public/images/guide_check_id.png">{$LANGDATA.iuav_click_here}</a> {$LANGDATA.iuav_place_hardware_id}</p>
                                        </div>
                                    </div>
                                    <div class="form-group essential" input-check="true">
                                        <label class="col-sm-4 control-label">{$LANGDATA.iuav_enter_serial_number}</label>
                                        <div class="col-sm-6">
                                           {literal} <input type="hidden" name="localid" value="{{localId}}"/> {/literal}
                                            <input type="text" class="form-control" name="body_code" />
                                            <p class="help-block">{$LANGDATA.iuav_packing_number}<a href="" data-toggle="modal" data-target="#viewModal" data-src="/public/images/guide_check_body_code.jpg">{$LANGDATA.iuav_click_here}</a>{$LANGDATA.iuav_aircraft_number}</p>
                                        </div>
                                    </div>

                                    <div class="form-group form-result-wrap">
                                        <div class="col-sm-offset-4 col-sm-6">
                                            <div class="alert alert-danger form-result"></div>
                                        </div>
                                    </div>
                                </div>
                           
                            </script>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-4">
                                <button type="button" class="button" id="addDevice">{$LANGDATA.iuav_add_equipment}</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-section" data-section="info">
                        <div class="form-title">
                            <span class="title">{$LANGDATA.iuav_user_info}</span>

                            <p class="help-tip">
                                <span class="text-danger">{$LANGDATA.iuav_please_note}</span>
                            </p>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-6">

                                <label class="radio-inline">
                                    <input type="radio" name="user_type" value="company" checked/> {$LANGDATA.iuav_compay_user}
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="user_type" value="personal"/> {$LANGDATA.iuav_individual_user}
                                </label>
                            </div>
                        </div>

                        <div class="form-group essential">
                            <label class="col-sm-4 control-label">{$LANGDATA.iuav_dji_account}</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="account" />

                                <p class="help-block text-danger">
                                    <span class="text-danger">{$LANGDATA.iuav_check_info}</span>
                                </p>
                            </div>
                        </div>

                        <div class="company-form" data-type="company">
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_company_name}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" name="company_name" />
                                    <p class="help-block">{$LANGDATA.iuav_same_company}</p>
                                </div>
                            </div>
                            {if $country eq 'cn'}
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_company_id}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" name="company_number" />
                                    <p class="help-block">{$LANGDATA.iuav_same_company_id}</p>
                                </div>
                            </div>
                            {/if}
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_company_phone}</label>
                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" name="telephone_1" />
                                        </div>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="telephone_2" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_company_address}</label>
                                <div class="col-sm-6">
                                    {if $country == 'cn'}
                                        <div class="geo" style="overflow: hidden; padding-bottom: 10px;">
                                            <div class="col-sm-3">
                                                <select class="form-control" geo-type="province" name="province"></select>
                                            </div>
                                            <div class="col-sm-3">
                                                <select class="form-control" geo-type="city" name="city"></select>
                                            </div>
                                            <div class="col-sm-3">
                                                <select class="form-control" geo-type="district" name="area"></select>
                                            </div>
                                            <div class="col-sm-3">
                                                <select class="form-control" geo-type="street" name="street"></select>
                                            </div>
                                        </div>

                                        <textarea rows="3" class="form-control" placeholder="{$LANGDATA.iuav_enter_address}" name="address"></textarea>
                                    {else}
                                        <p>
                                            <input type="text" class="form-control" name="street" placeholder="{$LANGDATA.iuav_input_address_1}" />
                                        </p>
                                        <p>
                                            <input type="text" class="form-control" name="district" placeholder="{$LANGDATA.iuav_input_address_2}" />
                                        </p>
                                        <p>
                                            <input type="text" class="form-control" name="city" placeholder="{$LANGDATA.iuav_input_city}" />
                                        </p>
                                        <p>
                                            <input type="text" class="form-control" name="province" placeholder="{$LANGDATA.iuav_input_province}" />
                                        </p>
                                        <p>
                                            <select name="country" class="form-control">
                                                <option value="at">Austria</option>
                                                <option value="be">Belgium</option>
                                                <option value="bg">Bulgaria</option>
                                                <option value="ca">Canada</option>
                                                <option value="cn">China</option>
                                                <option value="hr">Croatia</option>
                                                <option value="cy">Cyprus</option>
                                                <option value="cz">Czech Republic</option>
                                                <option value="dk">Denmark</option>
                                                <option value="ee">Estonia</option>
                                                <option value="fi">Finland</option>
                                                <option value="fr">France</option>
                                                <option value="de">Germany</option>
                                                <option value="gr">Greece</option>
                                                <option value="hk">Hong Kong</option>
                                                <option value="hu">Hungary</option>
                                                <option value="ie">Ireland</option>
                                                <option value="it">Italy</option>
                                                <option value="jp">Japan</option>
                                                <option value="lv">Latvia</option>
                                                <option value="li">Liechtenstein</option>
                                                <option value="lt">Lithuania</option>
                                                <option value="lu">Luxembourg</option>
                                                <option value="mo">Macau</option>
                                                <option value="my">Malaysia</option>
                                                <option value="mt">Malta</option>
                                                <option value="mx">Mexico</option>
                                                <option value="mc">Monaco</option>
                                                <option value="nl">Netherlands</option>
                                                <option value="nz">New Zealand</option>
                                                <option value="no">Norway</option>
                                                <option value="pl">Poland</option>
                                                <option value="pt">Portugal</option>
                                                <option value="pr">Puerto Rico</option>
                                                <option value="ro">Romania</option>
                                                <option value="sg">Singapore</option>
                                                <option value="sk">Slovakia</option>
                                                <option value="si">Slovenia</option>
                                                <option value="kr">South Korea</option>
                                                <option value="es">Spain</option>
                                                <option value="se">Sweden</option>
                                                <option value="ch">Switzerland</option>
                                                <option value="tw">Taiwan</option>
                                                <option value="gb">United Kingdom</option>
                                                <option value="us">United States</option>
                                            </select>
                                        </p>
                                    {/if}
                                </div>
                            </div>
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_equipment_manager_name}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" name="realname"/>
                                </div>
                            </div>
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_equipment_manager_card}</label>
                                <div class="col-sm-6">
                                    <input type="hidden" class="form-control" name="idcardtype" value="01"/>
                                    <input type="text" class="form-control" name="idcard"/>
                                </div>
                            </div>
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_equipment_manager_phone}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" name="phone"/>
                                </div>
                            </div>
                   
                        </div>

                        <div class="personal-form" data-type="personal">
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_user_name}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" name="realname"/>
                                </div>
                            </div>
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_user_card}</label>
                                <div class="col-sm-6">
                                    <input type="hidden" class="form-control" name="idcardtype" value="01"/>
                                    <input type="text" class="form-control" name="idcard"/>
                                </div>
                            </div>
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_address_3}</label>
                                <div class="col-sm-6">
                                    {if $country == 'cn'}
                                        <div class="geo" style="overflow: hidden; padding-bottom: 10px;">
                                            <div class="col-sm-3">
                                                <select class="form-control" geo-type="province" name="province"></select>
                                            </div>
                                            <div class="col-sm-3">
                                                <select class="form-control" geo-type="city" name="city"></select>
                                            </div>
                                            <div class="col-sm-3">
                                                <select class="form-control" geo-type="district" name="area"></select>
                                            </div>
                                            <div class="col-sm-3">
                                                <select class="form-control" geo-type="street" name="street"></select>
                                            </div>
                                        </div>

                                        <textarea rows="3" class="form-control" placeholder="{$LANGDATA.iuav_enter_address}" name="address"></textarea>
                                    {else}
                                        <p>
                                            <input type="text" class="form-control" name="street" placeholder="{$LANGDATA.iuav_input_address_1}" />
                                        </p>
                                        <p>
                                            <input type="text" class="form-control" name="district" placeholder="{$LANGDATA.iuav_input_address_2}" />
                                        </p>
                                        <p>
                                            <input type="text" class="form-control" name="city" placeholder="{$LANGDATA.iuav_input_city}" />
                                        </p>
                                        <p>
                                            <input type="text" class="form-control" name="province" placeholder="{$LANGDATA.iuav_input_province}" />
                                        </p>
                                        <p>
                                            <select name="country" class="form-control">
                                                <option value="at">Austria</option>
                                                <option value="be">Belgium</option>
                                                <option value="bg">Bulgaria</option>
                                                <option value="ca">Canada</option>
                                                <option value="cn">China</option>
                                                <option value="hr">Croatia</option>
                                                <option value="cy">Cyprus</option>
                                                <option value="cz">Czech Republic</option>
                                                <option value="dk">Denmark</option>
                                                <option value="ee">Estonia</option>
                                                <option value="fi">Finland</option>
                                                <option value="fr">France</option>
                                                <option value="de">Germany</option>
                                                <option value="gr">Greece</option>
                                                <option value="hk">Hong Kong</option>
                                                <option value="hu">Hungary</option>
                                                <option value="ie">Ireland</option>
                                                <option value="it">Italy</option>
                                                <option value="jp">Japan</option>
                                                <option value="lv">Latvia</option>
                                                <option value="li">Liechtenstein</option>
                                                <option value="lt">Lithuania</option>
                                                <option value="lu">Luxembourg</option>
                                                <option value="mo">Macau</option>
                                                <option value="my">Malaysia</option>
                                                <option value="mt">Malta</option>
                                                <option value="mx">Mexico</option>
                                                <option value="mc">Monaco</option>
                                                <option value="nl">Netherlands</option>
                                                <option value="nz">New Zealand</option>
                                                <option value="no">Norway</option>
                                                <option value="pl">Poland</option>
                                                <option value="pt">Portugal</option>
                                                <option value="pr">Puerto Rico</option>
                                                <option value="ro">Romania</option>
                                                <option value="sg">Singapore</option>
                                                <option value="sk">Slovakia</option>
                                                <option value="si">Slovenia</option>
                                                <option value="kr">South Korea</option>
                                                <option value="es">Spain</option>
                                                <option value="se">Sweden</option>
                                                <option value="ch">Switzerland</option>
                                                <option value="tw">Taiwan</option>
                                                <option value="gb">United Kingdom</option>
                                                <option value="us">United States</option>
                                            </select>
                                        </p>
                                    {/if}
                                </div>
                            </div>
                            <div class="form-group essential">
                                <label class="col-sm-4 control-label">{$LANGDATA.iuav_phone}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" name="phone" />
                                </div>
                            </div>                          
                        </div>

                        <div class="form-group form-button-group">
                            <div class="col-sm-offset-4 col-sm-6">
                                <div class="alert alert-danger" id="formResult" style="display: none;"></div>
                                <button type="submit" class="button">{$LANGDATA.iuav_next}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: none;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>

    <script type="text/html" id="tpl_activeSuccess">
        
        <div class="active-success-box">
            <p class="congrats">{$LANGDATA.iuav_congratulations} {literal} {{username}} {/literal} {$LANGDATA.iuav_details_submit}</p>

            <table class="table table-striped table-without-border apply-table">
                <thead>
                    <tr>
                        <th>{$LANGDATA.iuav_hardware_id}</th>
                        <th>{$LANGDATA.iuav_aircraft_number}</th>
                        <th>{$LANGDATA.iuav_active_code}</th>
                    </tr>
                </thead>
                <tbody>
                {literal}
                    {{each list as value i}}
                    <tr>
                        <td>{{value.hardware_id}}</td>
                        <td>{{value.body_code}}</td>
                        <td>{{value.activation}} <a href="javascript:;" class="copy icon-copy" data-clipboard-text="{{value.activation}}">{/literal}{$LANGDATA.iuav_copy}{literal}</a></td>
                    </tr>
                    {{/each}}
                {/literal}
                </tbody>
            </table>

            <div class="active-tip">
                <p>{$LANGDATA.iuav_connect_aricraft}</p>
                <p>
                    <a href="" data-toggle="modal" data-target="#viewModal" data-src="/public/images/guide_input_activation.jpg">{$LANGDATA.iuav_click_here}</a>{$LANGDATA.iuav_click_active_here}
                </p>
            </div>

            <div class="active-secure">
                <p>{$LANGDATA.iuav_auto_24}</p>
                <p>{$LANGDATA.iuav_active_schedule}</p>
                <p>{$LANGDATA.iuav_insurance_active}</p>
            </div>

            <p class="active-button">
                <a href="/adminagent/management" class="button button-default">{$LANGDATA.iuav_user_management}</a>
                <a href="/adminagent/" class="button button-default">{$LANGDATA.iuav_active_aircraft}</a>
            </p>
        </div>
        
    </script>
    <script type="text/javascript" data-main="/public/js/index.js?v=18" src="/public/js/externals/require-2.1.11.min.js"></script>
    {include "footer.tpl"}
</body>
</html>
