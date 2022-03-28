<form id="search" action="/" method='GET'>
    <input id="input" autocomplete="on" type="search" placeholder="ٱبحث ..." name="s" value="<?php the_search_query();?>" required/>
    <button id="button" type="submit" aria-label="Search">
      <i class="fa fa-search"></i>
    </button>

</form>