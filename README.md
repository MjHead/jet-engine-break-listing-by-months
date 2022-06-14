# JetEngine - break listing by months.

Allow to break single listing grid into sections separated by month based on post publication date or date from the meta field. Something like this:

![image](https://user-images.githubusercontent.com/4987981/172800275-70fae83f-b9c4-44cf-8f79-92ec4231f4a1.png)

Plugin works only with Query Builder, so you can break only listings where you get the posts with Query Builder

Also at the moment plugin works only with the Posts. But you can extend it by yourself for any object type you want (details in ths **Advanced** section)

And last note - plugin do not sort posts by date itself, it only adding breaks based on comparison of posts dates. So you need to sort post by your self with Query settings

## Setup
- Download and intall plugin,
- Define configuration constants in the end of functions.php file of your active theme,
- Add 'break_months' into Query ID option of Query builder (may be changed with configuration constants):
![image](https://user-images.githubusercontent.com/4987981/172801648-d3b6d752-4140-493e-ab88-d91833064f1b.png)

**Note!** If you using Listing Grid in combination with JetSmartFilters, you need to set 'break_months' also as listing ID and filter query ID

Configuration example:

``` php
  define( 'JET_ENGINE_BREAK_BY_FIELD', 'my_date_field' );
```

**Allowed constants:**

- `JET_ENGINE_BREAK_BY_FIELD` - by default `false` - breaks posts by publication date. You can set any meta field key you want insted to break by meta field,
- `JET_ENGINE_BREAK_BY_QUERY_ID` - by default 'break_months'. Trigger for breaking current listing
- `JET_ENGINE_BREAK_MONTH_OPEN_HTML` - by default `<h4 class="jet-engine-break-listing" style="width:100%; flex: 0 0 100%;">` - opening HTML markup for month name. Please note - "style="width:100%; flex: 0 0 100%;" is important for multicolumn layout
- `JET_ENGINE_BREAK_MONTH_CLOSE_HTML` - by default `</h4>` - closing HTML markup
- `JET_ENGINE_BREAK_MONTH_FORMAT` - by default 'F, Y'. Date format string. Allowed merkup here - https://www.php.net/manual/en/datetime.format.php

## Advanced

To exted plugin functionality to any object you want, you need to rewrite getting data part - https://github.com/MjHead/jet-engine-break-listing-by-months/blob/master/jet-engine-break-listing-by-months.php#L99-L102

- For CCT created date you can get with `$post->cct_created`, custom field accessible by its name - `$post->my_field`
- For terms and users you need to use get_term_meta and get_user_meta functions
