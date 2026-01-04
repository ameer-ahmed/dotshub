<?php

namespace App\Enums;

use App\Traits\Enumable;

enum QuestionType: string
{
    use Enumable;

    case CHECKBOX = 'CHECKBOX';
    case SELECT = 'SELECT';
    case MULTI_SELECT = 'MULTI_SELECT';
    case RADIO = 'RADIO';
    case TEXT = 'TEXT';
    case TEXT_AREA = 'TEXT_AREA';
    case DOCUMENTS = 'DOCUMENTS';
    case DATE = 'DATE';
    case DATE_RANGE = 'DATE_RANGE';
    case EMAIL = 'EMAIL';
    case MOBILE = 'MOBILE';
    case NUMBER = 'NUMBER';
    case COUNTER = 'COUNTER';
    case MAP = 'MAP';
    case LOGO = 'LOGO';
    case IMAGES = 'IMAGES';
    case VIDEOS = 'VIDEOS';
    case UNIQUE_ID = 'UNIQUE_ID';
    case TO_MAIL = 'TO_MAIL';
    case CC_MAIL = 'CC_MAIL';
    case CKEditor = 'CKEditor';
    case DWCKEditor = 'DWCKEditor';
    case THREE_D_FUNCTION = '3D_FUNCTION';
    case THREE_SIXTY_FUNCTION = '360_FUNCTION';
    case PRICE = 'PRICE';
    case PERCENT = 'PERCENT';
    case HYPERLINK = 'HYPERLINK';

    public function validationRules(): array
    {
        return match ($this) {
            self::CHECKBOX, self::MULTI_SELECT => ['array'],
            self::SELECT => ['string'],
            self::RADIO => ['string'],
            self::TEXT => ['string', 'max:255'],
            self::TEXT_AREA => ['string', 'max:5000'],
            self::DOCUMENTS => ['file', 'mimes:pdf,doc,docx,xls,xlsx,txt', 'max:10240'],
            self::DATE => ['date'],
            self::DATE_RANGE => ['array', 'size:2'],
            self::EMAIL => ['email', 'max:255'],
            self::MOBILE => ['string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:20'],
            self::NUMBER => ['numeric'],
            self::COUNTER => ['integer', 'min:0'],
            self::MAP => ['array'],
            self::LOGO => ['image', 'mimes:jpeg,jpg,png,svg', 'max:5120'],
            self::IMAGES => ['array'],
            self::VIDEOS => ['array'],
            self::UNIQUE_ID => ['string', 'max:255'],
            self::TO_MAIL => ['email', 'max:255'],
            self::CC_MAIL => ['email', 'max:255'],
            self::CKEditor => ['string'],
            self::DWCKEditor => ['string'],
            self::THREE_D_FUNCTION => ['file', 'mimes:glb,gltf,obj,fbx', 'max:51200'],
            self::THREE_SIXTY_FUNCTION => ['file', 'mimes:jpg,jpeg,png', 'max:20480'],
            self::PRICE => ['numeric', 'min:0'],
            self::PERCENT => ['numeric', 'min:0', 'max:100'],
            self::HYPERLINK => ['url', 'max:2048'],
        };
    }

    public function translated(): string
    {
        return match ($this) {
            self::CHECKBOX => __('question_types.checkbox'),
            self::SELECT => __('question_types.select'),
            self::MULTI_SELECT => __('question_types.multi_select'),
            self::RADIO => __('question_types.radio'),
            self::TEXT => __('question_types.text'),
            self::TEXT_AREA => __('question_types.text_area'),
            self::DOCUMENTS => __('question_types.documents'),
            self::DATE => __('question_types.date'),
            self::DATE_RANGE => __('question_types.date_range'),
            self::EMAIL => __('question_types.email'),
            self::MOBILE => __('question_types.mobile'),
            self::NUMBER => __('question_types.number'),
            self::COUNTER => __('question_types.counter'),
            self::MAP => __('question_types.map'),
            self::LOGO => __('question_types.logo'),
            self::IMAGES => __('question_types.images'),
            self::VIDEOS => __('question_types.videos'),
            self::UNIQUE_ID => __('question_types.unique_id'),
            self::TO_MAIL => __('question_types.to_mail'),
            self::CC_MAIL => __('question_types.cc_mail'),
            self::CKEditor => __('question_types.ckeditor'),
            self::DWCKEditor => __('question_types.dw_ckeditor'),
            self::THREE_D_FUNCTION => __('question_types.three_d_function'),
            self::THREE_SIXTY_FUNCTION => __('question_types.three_sixty_function'),
            self::PRICE => __('question_types.price'),
            self::PERCENT => __('question_types.percent'),
            self::HYPERLINK => __('question_types.hyperlink'),
        };
    }

    public function fullTranslated(): array
    {
        return match ($this) {
            self::CHECKBOX => [
                'en' => __('question_types.checkbox', [], 'en'),
                'ar' => __('question_types.checkbox', [], 'ar'),
            ],
            self::SELECT => [
                'en' => __('question_types.select', [], 'en'),
                'ar' => __('question_types.select', [], 'ar'),
            ],
            self::MULTI_SELECT => [
                'en' => __('question_types.multi_select', [], 'en'),
                'ar' => __('question_types.multi_select', [], 'ar'),
            ],
            self::RADIO => [
                'en' => __('question_types.radio', [], 'en'),
                'ar' => __('question_types.radio', [], 'ar'),
            ],
            self::TEXT => [
                'en' => __('question_types.text', [], 'en'),
                'ar' => __('question_types.text', [], 'ar'),
            ],
            self::TEXT_AREA => [
                'en' => __('question_types.text_area', [], 'en'),
                'ar' => __('question_types.text_area', [], 'ar'),
            ],
            self::DOCUMENTS => [
                'en' => __('question_types.documents', [], 'en'),
                'ar' => __('question_types.documents', [], 'ar'),
            ],
            self::DATE => [
                'en' => __('question_types.date', [], 'en'),
                'ar' => __('question_types.date', [], 'ar'),
            ],
            self::DATE_RANGE => [
                'en' => __('question_types.date_range', [], 'en'),
                'ar' => __('question_types.date_range', [], 'ar'),
            ],
            self::EMAIL => [
                'en' => __('question_types.email', [], 'en'),
                'ar' => __('question_types.email', [], 'ar'),
            ],
            self::MOBILE => [
                'en' => __('question_types.mobile', [], 'en'),
                'ar' => __('question_types.mobile', [], 'ar'),
            ],
            self::NUMBER => [
                'en' => __('question_types.number', [], 'en'),
                'ar' => __('question_types.number', [], 'ar'),
            ],
            self::COUNTER => [
                'en' => __('question_types.counter', [], 'en'),
                'ar' => __('question_types.counter', [], 'ar'),
            ],
            self::MAP => [
                'en' => __('question_types.map', [], 'en'),
                'ar' => __('question_types.map', [], 'ar'),
            ],
            self::LOGO => [
                'en' => __('question_types.logo', [], 'en'),
                'ar' => __('question_types.logo', [], 'ar'),
            ],
            self::IMAGES => [
                'en' => __('question_types.images', [], 'en'),
                'ar' => __('question_types.images', [], 'ar'),
            ],
            self::VIDEOS => [
                'en' => __('question_types.videos', [], 'en'),
                'ar' => __('question_types.videos', [], 'ar'),
            ],
            self::UNIQUE_ID => [
                'en' => __('question_types.unique_id', [], 'en'),
                'ar' => __('question_types.unique_id', [], 'ar'),
            ],
            self::TO_MAIL => [
                'en' => __('question_types.to_mail', [], 'en'),
                'ar' => __('question_types.to_mail', [], 'ar'),
            ],
            self::CC_MAIL => [
                'en' => __('question_types.cc_mail', [], 'en'),
                'ar' => __('question_types.cc_mail', [], 'ar'),
            ],
            self::CKEditor => [
                'en' => __('question_types.ckeditor', [], 'en'),
                'ar' => __('question_types.ckeditor', [], 'ar'),
            ],
            self::DWCKEditor => [
                'en' => __('question_types.dw_ckeditor', [], 'en'),
                'ar' => __('question_types.dw_ckeditor', [], 'ar'),
            ],
            self::THREE_D_FUNCTION => [
                'en' => __('question_types.three_d_function', [], 'en'),
                'ar' => __('question_types.three_d_function', [], 'ar'),
            ],
            self::THREE_SIXTY_FUNCTION => [
                'en' => __('question_types.three_sixty_function', [], 'en'),
                'ar' => __('question_types.three_sixty_function', [], 'ar'),
            ],
            self::PRICE => [
                'en' => __('question_types.price', [], 'en'),
                'ar' => __('question_types.price', [], 'ar'),
            ],
            self::PERCENT => [
                'en' => __('question_types.percent', [], 'en'),
                'ar' => __('question_types.percent', [], 'ar'),
            ],
            self::HYPERLINK => [
                'en' => __('question_types.hyperlink', [], 'en'),
                'ar' => __('question_types.hyperlink', [], 'ar'),
            ],
        };
    }

    public static function hasRequiredOptions()
    {
        return [self::CHECKBOX->value, self::RADIO->value, self::MULTI_SELECT->value, self::SELECT->value];
    }
}
