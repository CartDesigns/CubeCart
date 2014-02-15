<div id="mini-basket">
   <a href="#" id="basket-summary"><i class="fa fa-shopping-cart"></i> {$CART_TOTAL}</a> 
   <div class="hide basket-detail-container" id="basket-detail">
      <div class="mini-basket-arrow"></div>
      <h4 class="mini-basket-title nomarg pad-side">{$LANG.basket.your_basket}</h4>
      <div class="pad-side basket-detail">
         {if isset($CONTENTS) && count($CONTENTS) > 0}
         {foreach from=$CONTENTS item=item}
         <p class="clearfix">
           <div class="left"><a href="{$item.link}" title="{$item.name}">{$item.quantity} &times; {$item.name|truncate:25:"&hellip;"}</a></div>
           <div class="right">{$item.total}</div>
         </p>
         {/foreach}
         <p class="clearfix">
           <div class="left">{$LANG.common.item_plural}:</div>
           <div class="right">{$CART_ITEMS}</div>
         </p>
         <p class="clearfix">
           <div class="left total">{$LANG.basket.total}:</div>
           <div class="right total">{$CART_TOTAL}</div>
         </p>
         <div><a href="?_a=checkout" class="button expand marg-top">{$LANG.basket.basket_checkout}</a></div>
         <div><a href="?_a=basket" class="button secondary expand">{$LANG.basket.view_basket}</a></div>
         {else}
         <p class="pad-top text-center">{$LANG.basket.basket_is_empty}</p>
         {/if}
      </div>
   </div>
</div>
