<?php
/* ==================================
    programmer : Saiful Amin
    module     : BaseModel
    created_at : 2022-12-20
  ================================== */
namespace Pakdhe\Translator\Models;

class BaseModel extends Illuminate\Database\Eloquent\Model
{
    use ModelTransaction;
    protected $is_translate = false;

    public function scopeSearch($query, $keyword)
    {
        if ($keyword && $this->searchableColumns) {
            $keyword = strtolower($keyword);
            foreach($this->searchableColumns as $attribute) {
                $query->orWhereRaw("lower($attribute) LIKE '%{$keyword}%'");
            }
        }
    }

    //Translate
    public function translate()
    {
        $value = $this;
        $langcode = request()->get('lang_code') ?? (env("BAHASA") ?? "id");
        if($langcode !== 'id' && $this->is_translate){
            $table = $this->getTable();
            $column_tables = \DB::getSchemaBuilder()->getColumnListing($table);
            $translates = Translation::where(['table_reference' => $table, 'remote_id' => $this->id, 'lang_code' => $langcode])->get();
            if(isset($translates) && !$translates->isEmpty()){
                foreach($translates as $translate){
                    $key = array_search($translate->remote_field, $column_tables); // return index or false
                    if($key !== false){
                        $field = $translate->remote_field;
                        if(isset($field)){
                            $value->$field = $translate->value ?? $this->$field;
                        }
                    }
                }
            }
        }
        return $value;
    }
    public static function loaded($callback)
    {
        static::registerModelEvent('loaded', $callback);
    }

    public function getObservableEvents()
    {
        return array_merge(parent::getObservableEvents(), array('loaded'));
    }

    public function newFromBuilder($attributes = array(), $connection = NULL)
    {
        $instance = parent::newFromBuilder($attributes);
        $instance->fireModelEvent('loaded', false);
        return $instance->translate();
    }
    //Translate

}
