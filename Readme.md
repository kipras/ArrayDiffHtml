# ArrayDiffHtml

PHP class for pretty-printing the difference between two arrays/objects using HTML, CSS and JavaScript,
providing buttons to expand/collapse each level.

Example screenshot:  
![ArrayDiffHtml screenshot](http://www.kipras.com/kipras_libs/ArrayDiffHtml.png)

## Usage

- **ArrayDiffHtml::diff($first, $second, [$strictEquality], [$opt])**

    Options can be passed as an associative array in the $opt parameter.  
    Available options:
    
    - `title1`: Title of the `$first1` array (default = _'First'_)
    - `title2`: Title of the `$second` array (default = _'Second'_)
    - `noSecond`: If there is no second array (we are only printing the contents of the first array
      and no comparison should be done) - this should be set to _`True`_. (default = _False_)

## Requirements

* PHP >= 5.3 (uses `static::` keyword)
