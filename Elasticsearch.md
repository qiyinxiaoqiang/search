
#	Elasticsearch搜索
###	中文版：https://es.xiaoleilu.com/
>*参考：
```base
http://www.cnblogs.com/amuge/p/6076232.html
http://www.jianshu.com/p/eb30eee13923
http://www.cnblogs.com/ghj1976/p/5293250.html
http://blog.csdn.net/dm_vincent/article/details/41842691
```


>*	列举：

```php
<?php
$json = '{
	"from": '.($params['page'] - 1)*$params['limit'].', "size": '.$params['limit'].',
	"query": {
		"bool": {
			"must": [
				{ "match_phrase": { "user_id":   '.$params['user_id'].' }},
				{"multi_match" : {
					"query":      "'.$params['key'].'",
					"type":       "best_fields",
					"fields":     ["body^3", "title"],
					"operator":   "and" 
				}},
				{ "match_phrase": { "is_publish":  "y" }},
				{ "match_phrase": { "is_recyle":  "n" }},
				{ "match_phrase": { "is_del":  "n" }},
				{ "match_phrase": { "monitor.is_audit":  "y" }},
				{ "match_phrase": { "monitor.hidden.is":  "n" }},
				{ "match_phrase": { "monitor.del.is":  "n" }}
			]
		}
	},//form:分页,match_phrase:指定的条件,multi_match：查询提供了一个简便的方法用来对多个字段执行相同的查询（query：查询的内容，type：包含best_fields，most_fields，cross_fields，phrase，phrase_prefix）详见d1,fields:需查询的字段,operator:该参数的默认值是"or"。你可以将它改变为"and"来要求所有的词条都需要被匹配
	"highlight" : {
		"pre_tags" : ["<em class=\"search_word\">"],
		"post_tags" : ["</em>"],
		"fields" : {
			"title" : {},
			"body": {}
		}
	}//highlight:字段高亮设置
}';
$arr = [
	'index' => 'v5_uc',
	'type' => '_search',
	'body' => $json
];//index:索引 type:类型
$client = Elasticsearch::create()->setHosts(self::$host)->build();
$response = $client->index($arr);
$count = $response['hits']['total'];//查询的总条数
$data = $response['hits']['hits'];//数组数据内容
?>

 
```

###	d1:
```base

内部执行multi_match查询的方式依赖于type参数，它可以被设置成：

　　best_fields  　　（默认）查找与任何字段匹配的文档，但使用最佳字段中的_score。看best_fields.

　　most_fields　　查找与任何字段匹配的文档，并联合每个字段的_score.

　　cross_fields　　采用相同分析器处理字段，就好像他们是一个大的字段。在每个字段中查找每个单词。看cross_fields。

　　phrase　　　　在每个字段上运行match_phrase查询并和每个字段的_score组合。看phrase and phrase_prefix。

　　phrase_prefix    在每个字段上运行match_phrase_prefix查询并和每个字段的_score组合。看phrase and phrase_prefix。

best_fields

　　当你在同一个字段中搜索最佳查找的多个单词时，bese_fields类型是最有效的。例如，"brown fox"单独在一个字段中比"brown"在一个字段中和"for"在另外一个字段中更有意义。
```