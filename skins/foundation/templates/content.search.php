<form method="post" action="?_a=category" enctype="multipart/form-data">
   <h2>{$LANG.search.advanced}</h2>
   <div class="row">
      <div class="large-8 columns"><label for="keywords">{$LANG.search.keywords}</label><input type="text" class="required" name="search[keywords]" placeholder="{$LANG.search.keywords}" id="keywords" /></div>
   </div>
   <div class="row">
      <div class="large-8 columns">
         <label for="">{$LANG.search.price_range}</label>
      </div>
   </div>
   <div class="row">
      <div class="large-2 columns">
         <input type="text" name="search[priceMin]" placeholder="{$LANG.common.from}" />
      </div>
      <div class="large-1 columns text-center">-</div>
      <div class="large-2 columns">
         <input type="text" name="search[priceMax]" placeholder="{$LANG.common.to}" /> 
      </div>
      <div class="large-3 large-pull-4 columns">
         <input type="checkbox" name="search[priceVary]" value="1" /> &plusmn;5%
      </div>
   </div>
   {if isset($MANUFACTURERS)}
   <div class="row">
      <div class="large-8 columns">
         <label for="">{$LANG.catalogue.manufacturer}</label>
      </div>
   </div>
   <div class="row">
      <div class="large-12 columns">
         <ul class="small-block-grid-5">
            {foreach from=$MANUFACTURERS item=manufacturer}
            <li><input type="checkbox" value="{$manufacturer.id}" id="manufacturer_{$manufacturer.id}" name="search[manufacturer][]" {$manufacturer.selected} /><label for="manufacturer_{$manufacturer.id}">{$manufacturer.name}</label></li>
            {/foreach}
         </ul>
      </div>
   </div>
   {/if}
   {if isset($SORTERS)}
   <div class="row">
      <div class="large-8 columns">
         <label for="sort">{$LANG.form.sort_by}</label>
         <select name="sort" id="sort">
         {foreach from=$SORTERS item=sort}
         <option value="{$sort.field}|{$sort.order}" {$sort.selected}>{$sort.name} ({$sort.direction})</option>
         {/foreach}
         </select>
      </div>
   </div>
   {/if}
   {if !isset($OUT_OF_STOCK)}
   <div class="row">
      <div class="large-8 columns"><input type="checkbox" name="search[inStock]" id="in_stock" value="1" /><label for="in_stock">{$LANG.search.in_stock}</label></div>
   </div>
   {/if}
   <div class="row">
      <div class="large-8 columns"><input type="checkbox" name="search[featured]" id="featured_only" value="1" /><label for="featured_only">{$LANG.search.featured_only}</label></div>
   </div>
   <div class="clearfix">
      <input type="submit" class="button" value="{$LANG.form.submit}" /> <input type="reset" class="button secondary right" value="{$LANG.common.reset}" />
   </div>
</form>