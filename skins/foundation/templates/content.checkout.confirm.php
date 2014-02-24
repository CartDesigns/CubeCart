{if $IS_USER}
<h2>
   <span class="inline"><a href="{$STORE_URL}/index.php?_a=addressbook&amp;action=edit&amp;address_id={$DATA.address_id}&amp;redir=confirm">{$LANG.address.address_edit}</a></span>
   {if $CTRL_DELIVERY}{$LANG.address.billing_address}{else}{$LANG.address.billing_delivery_address}{/if}
</h2>
<p>
   {$DATA.title} {$DATA.first_name} {$DATA.last_name}<br />
   {if $DATA.company_name}{$DATA.company_name}<br />{/if}
   {$DATA.line1}<br />
   {if $DATA.line2}{$DATA.line2}<br />{/if}
   {$DATA.town}<br />
   {$DATA.state}, {$DATA.postcode}<br />
   {$DATA.country}<br />
</p>
{if $CTRL_DELIVERY}
<h2>{$LANG.address.delivery_address}
   <span class="inline"><a href="{$STORE_URL}/index.php?_a=addressbook&amp;action=add&amp;redir=confirm">{$LANG.address.address_add}</a></span>
</h2>
<p>
   <select name="delivery_address" id="delivery_address" class="update_form" style="border: 1px solid #222222; width: 400px;">
   {foreach from=$ADDRESSES item=address}
   <option value="{$address.address_id}" {$address.selected}>{$address.description} - {$address.first_name} {$address.last_name}, {$address.line1}, {$address.postcode}</option>
   {/foreach}
   </select>
</p>
{/if}
{else}
<div id="register">
   <p>{$LANG.account.already_registered} <a href="{$URL.login}">{$LANG.account.log_in}</a></p>
   <h2>{$LANG.account.your_details}</h2>
   <h3>{$LANG.account.contact_details}</h3>
   <div class="row">
      <div class="small-4 columns"><label for="user_title">{$LANG.user.title}</label><input type="text" name="user[title]" id="user_title"  class="capitalize" value="{$USER.title}" placeholder="{$LANG.user.title}" /></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="user_first">{$LANG.user.name_first}</label><input type="text" name="user[first_name]" id="user_first"   required value="{$USER.first_name}" placeholder="{$LANG.user.name_first}  {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="user_last">{$LANG.user.name_last}</label><input type="text" name="user[last_name]" id="user_last"   required value="{$USER.last_name}" placeholder="{$LANG.user.name_last}  {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="user_email">{$LANG.common.email}</label><input type="text" name="user[email]" id="user_email"  required value="{$USER.email}" placeholder="{$LANG.common.email}  {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="user_phone">{$LANG.address.phone}</label><input type="text" name="user[phone]" id="user_phone"  required value="{$USER.phone}" placeholder="{$LANG.address.phone}  {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="user_mobile">{$LANG.address.mobile}</label><input type="text" name="user[mobile]" id="user_mobile"  value="{$USER.mobile}" placeholder="{$LANG.address.mobile}" /></div>
   </div>
   <h3>{$LANG.address.billing_address}</h3>
   {if !$ALLOW_DELIVERY_ADDRESS}{$LANG.address.ship_to_billing_only}{/if}
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_company">{$LANG.address.company_name}</label><input type="text" name="billing[company_name]" id="addr_company"  value="{$BILLING.company_name}" placeholder="{$LANG.address.company_name}" /></div>
   </div>
   <address>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_line1">{$LANG.address.line1}</label><input type="text" name="billing[line1]" id="addr_line1"   value="{$BILLING.line1}" placeholder="{if $ADDRESS_LOOKUP}{$LANG.address.address_lookup}{else}{$LANG.address.line1} {$LANG.form.required}{/if}" autocomplete="off" autocorrect="off" class="address_lookup"></div>
   </div>
   <div{if $ADDRESS_LOOKUP} class="hide"{/if} id="address_form">
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_line2">{$LANG.address.line2}</label><input type="text" name="billing[line2]" id="addr_line2"  value="{$BILLING.line2}" placeholder="{$LANG.address.line2}" /></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_town">{$LANG.address.town}</label><input type="text" name="billing[town]" id="addr_town"  required value="{$BILLING.town}" placeholder="{$LANG.address.town} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_postcode">{$LANG.address.postcode}</label><input type="text" name="billing[postcode]" id="addr_postcode"  class="uppercase required" value="{$BILLING.postcode}" placeholder="{$LANG.address.postcode} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="country-list">{$LANG.address.country}</label>
         <select name="billing[country]"  id="country-list">
         {foreach from=$COUNTRIES item=country}
         <option value="{$country.numcode}" {$country.selected}>{$country.name}</option>
         {/foreach}
         </select>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="state-list">{$LANG.address.state}</label></span><input type="text" name="billing[state]" id="state-list"  required value="{$BILLING.state}"></div>
   </div>
   </div>
   </address>
   {if $TERMS_CONDITIONS}
   <div class="row">
      <div class="small-12 large-8 columns"><input type="checkbox" id="reg_terms" name="terms_agree" value="1" {$TERMS_CONDITIONS_CHECKED} /><label for="reg_terms">{$LANG.account.register_terms_agree_link|replace:'%s':{$TERMS_CONDITIONS}}</label></div>
   </div>
   {/if}
   {if $ALLOW_DELIVERY_ADDRESS}
   <div class="row">
      <div class="small-12 large-8 columns"><input type="checkbox" name="delivery_is_billing" id="delivery_is_billing" {$DELIVERY_CHECKED}><label for="delivery_is_billing">{$LANG.address.delivery_is_billing}</label></div>
   </div>
   {/if}
   {if $ALLOW_DELIVERY_ADDRESS}
   <div class="hide" id="address_delivery">
   <h3>{$LANG.address.delivery_address}</h3>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="del_first">{$LANG.user.name_first}</label><input type="text" name="delivery[first_name]" id="del_first"   required value="{$DELIVERY.first_name}" placeholder="{$LANG.user.name_first} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="del_last">{$LANG.user.name_last}</label><input type="text" name="delivery[last_name]" id="del_last"   required value="{$DELIVERY.last_name}" placeholder="{$LANG.user.name_last} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="del_company">{$LANG.address.company_name}</label><input type="text" name="delivery[company_name]" id="del_company"  value="{$DELIVERY.company_name}" placeholder="{$LANG.user.company_name}" /></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="del_line1">{$LANG.address.line1}</label><input type="text" name="delivery[line1]" id="del_line1"  required value="{$DELIVERY.line1}" placeholder="{$LANG.address.line1} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="del_line2">{$LANG.address.line2}</label><input type="text" name="delivery[line2]" id="del_line2"  value="{$DELIVERY.line2}" placeholder="{$LANG.address.line2}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="del_town">{$LANG.address.town}</label><input type="text" name="delivery[town]" id="del_town"  required value="{$DELIVERY.town}" placeholder="{$LANG.address.town} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="del_postcode">{$LANG.address.postcode}</label><input type="text" name="delivery[postcode]" id="del_postcode"  class="uppercase required" value="{$DELIVERY.postcode}" placeholder="{$LANG.address.postcode} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="delivery_country">{$LANG.address.country}</label>
         <select name="delivery[country]" id="delivery_country"  class="country-list" rel="delivery_state">
         {foreach from=$COUNTRIES item=country}
         <option value="{$country.numcode}" {$country.selected_d}>{$country.name}</option>
         {/foreach}
         </select>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="delivery_state">{$LANG.address.state}</label></span><input type="text" name="delivery[state]" id="delivery_state"  required value="{$DELIVERY.state}" placeholder="{$LANG.address.state} {$LANG.form.required}"></div>
   </div>
   </div>
   {/if}
   <script type="text/javascript">
      var county_list = {$STATE_JSON};
   </script>
   <div class="row"><div class="small-12 large-8 columns"><input type="checkbox" name="register" id="show-reg" value="1" {$REGISTER_CHECKED} /><label for="show-reg">{$LANG.account.create_account}</label></div></div>
   <div id="account-reg">
      <h3>{$LANG.account.password}</h3>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="reg_password">{$LANG.user.password}</label></span><input type="password" autocomplete="off" name="password" id="reg_password"  required value="" placeholder="{$LANG.address.password} {$LANG.form.required}"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="reg_passconf">{$LANG.user.password_confirm}</label></span><input type="password" autocomplete="off" name="passconf" id="reg_passconf"  required value="" placeholder="{$LANG.address.password_confirm} {$LANG.form.required}"></div>
      </div>
      </div>
   {include file='templates/content.recaptcha.php'}
</div>
{/if}
<p><label for="delivery_comments" class="return"><strong>{$LANG.basket.your_comments}</strong></label><textarea name="comments" id="delivery_comments">{$VAL_CUSTOMER_COMMENTS}</textarea></p>
<div class="hide" id="validate_required">{$LANG.form.required}</div>
