<?php

namespace Repeaterly\Includes;

class Hook {
    //action hooks
    const WIDGET_REGISTERED = 'repeaterly/widgets/registered';
    const CATEGORY_REGISTERED = 'repeaterly/categories/registered';
    
    //filter hooks
    const DYNAMIC_TEXT_OPTIONS = 'repeaterly/dynamic/sources/text';
    const DYNAMIC_LINK_OPTIONS = 'repeaterly/dynamic/sources/link';
    const DYNAMIC_IMAGE_OPTIONS = 'repeaterly/dynamic/sources/image';
    const DYNAMIC_VALUE = 'repeaterly/dynamic/value';
}