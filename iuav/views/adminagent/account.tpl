<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>{$LANGDATA.iuav_title}</title>

    <link rel="stylesheet" type="text/css" href="/public/css/externals/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="/public/css/common.css?v=17"/>
    <style type="text/css">
        {literal}
        .geo > div {
            padding: 0;
        }

        #applyModal .modal-header {
            border: none;
        }
        #applyModal .modal-title {
            margin-top: 20px;
        }
        #applyModal .form-result {
            display: none;
        }
        {/literal}
    </style>
</head>
<body class="{$country}">

    <div class="page-container">
        {include "left_side.tpl" nav="account"}

        <div class="main-side">
            {include "top.tpl" name="大疆创新科技有限公司"}

            <div class="main-body">
                <div class="apply-section">
                    <a href="javascript:;" class="button apply-button" id="applyButton">{$LANGDATA.iuav_create_account}</a>

                    <div class="list-section">
                        <div class="list-section-head">
                            <p>{$LANGDATA.iuav_apply_record}</p>
                        </div>
                        <div class="list-section-body">
                            <table class="table table-striped table-without-border apply-table">
                                <thead>
                                    <tr>
                                        <th>{$LANGDATA.iuav_no}</th>
                                        <th>{$LANGDATA.iuav_apply_date}</th>
                                        <th>{$LANGDATA.iuav_branch_name_2}</th>
                                        <th>{$LANGDATA.iuav_address}</th>
                                        <th>{$LANGDATA.iuav_manager}</th>
                                        <th>{$LANGDATA.iuav_phone}</th>
                                        <th>{$LANGDATA.iuav_email}</th>
                                        <th>{$LANGDATA.iuav_status}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if isset($agengData)}
                                    {foreach from=$agengData key="mykey" item=activedata}
                                        <tr>
                                            <td>{$mykey+1}</td>
                                            <td>{$activedata.created_at}</td>
                                            <td>{$activedata.agentname|escape:"html"}</td>
                                            <td>{$activedata.address|escape:"html"}</td>
                                            <td>{$activedata.realname|escape:"html"}</td>
                                            <td>{$activedata.phone|escape:"html"}</td>
                                            <td>{$activedata.email|escape:"html"}</td>
                                            <td{if $activedata.status eq 'agree'} class="status-approved"{/if}>
                                                {if $activedata.status eq 'pending'}
                                                    {$LANGDATA.iuav_review}
                                                {elseif $activedata.status eq 'agree' }
                                                    {$LANGDATA.iuav_approved}
                                                {else}
                                                    {$LANGDATA.iuav_rejected}
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                    {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="applyModal" tabindex="-1" role="dialog" aria-labelledby="applyModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-center" id="myModalLabel">{$LANGDATA.iuav_create_account}</h4>
                </div>
                <div class="modal-body">
                    <form method="post" action="/adminagent/addagentchild" class="form-horizontal" id="applyAccountForm">
                        <input type="hidden" name="_csrf" value="{$csrftoken}" ／>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{$LANGDATA.iuav_branch_name}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="agentname" placeholder="{$LANGDATA.iuav_service_station}"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">{$LANGDATA.iuav_manager}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="realname"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{$LANGDATA.iuav_phone}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="phone"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{$LANGDATA.iuav_login_email}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="email"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{$LANGDATA.iuav_address_2}</label>
                            <div class="col-sm-8">
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
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-8">
                                <div class="alert alert-danger form-result" id="formResult"></div>
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="button button-default">{$LANGDATA.iuav_confirm_submit}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" data-main="/public/js/apply.js?v=17" src="/public/js/externals/require-2.1.11.min.js"></script>
    {include "footer.tpl"}
</body>
</html>