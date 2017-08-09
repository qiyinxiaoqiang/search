
#	Elasticsearch����
###	���İ棺https://es.xiaoleilu.com/
>*�ο���
```base
http://www.cnblogs.com/amuge/p/6076232.html
http://www.jianshu.com/p/eb30eee13923
http://www.cnblogs.com/ghj1976/p/5293250.html
http://blog.csdn.net/dm_vincent/article/details/41842691
```


>*	�о٣�

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
	},//form:��ҳ,match_phrase:ָ��������,multi_match����ѯ�ṩ��һ�����ķ��������Զ���ֶ�ִ����ͬ�Ĳ�ѯ��query����ѯ�����ݣ�type������best_fields��most_fields��cross_fields��phrase��phrase_prefix�����d1,fields:���ѯ���ֶ�,operator:�ò�����Ĭ��ֵ��"or"������Խ����ı�Ϊ"and"��Ҫ�����еĴ�������Ҫ��ƥ��
	"highlight" : {
		"pre_tags" : ["<em class=\"search_word\">"],
		"post_tags" : ["</em>"],
		"fields" : {
			"title" : {},
			"body": {}
		}
	}//highlight:�ֶθ�������
}';
$arr = [
	'index' => 'v5_uc',
	'type' => '_search',
	'body' => $json
];//index:���� type:����
$client = Elasticsearch::create()->setHosts(self::$host)->build();
$response = $client->index($arr);
$count = $response['hits']['total'];//��ѯ��������
$data = $response['hits']['hits'];//������������
?>

 
```

###	d1:
```base

�ڲ�ִ��multi_match��ѯ�ķ�ʽ������type�����������Ա����óɣ�

����best_fields  ������Ĭ�ϣ��������κ��ֶ�ƥ����ĵ�����ʹ������ֶ��е�_score����best_fields.

����most_fields�����������κ��ֶ�ƥ����ĵ���������ÿ���ֶε�_score.

����cross_fields����������ͬ�����������ֶΣ��ͺ���������һ������ֶΡ���ÿ���ֶ��в���ÿ�����ʡ���cross_fields��

����phrase����������ÿ���ֶ�������match_phrase��ѯ����ÿ���ֶε�_score��ϡ���phrase and phrase_prefix��

����phrase_prefix    ��ÿ���ֶ�������match_phrase_prefix��ѯ����ÿ���ֶε�_score��ϡ���phrase and phrase_prefix��

best_fields

����������ͬһ���ֶ���������Ѳ��ҵĶ������ʱ��bese_fields����������Ч�ġ����磬"brown fox"������һ���ֶ��б�"brown"��һ���ֶ��к�"for"������һ���ֶ��и������塣
```