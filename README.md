### API
#### Add database
```
POST: {url}/wp-josn/ezimport/v1/data
```
Params:
```
{
    "table": "posts",
    "fields": "ID,post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count",
    "values": [
        "93, 1, '2022-05-25 00:46:59', '2022-05-25 00:46:59', '', 'T_2_front.jpg', '', 'inherit', 'open', 'closed', '', 't_2_front-jpg-24', '', '', '2022-05-25 00:46:59', '2022-05-25 00:46:59', '', 0, 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg', 0, 'attachment', 'image/jpeg', 0",
        "94, 1, '2022-05-25 00:47:01', '2022-05-25 00:47:01', '', 'T_2_back.jpg', '', 'inherit', 'open', 'closed', '', 't_2_back-jpg-24', '', '', '2022-05-25 00:47:01', '2022-05-25 00:47:01', '', 0, 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg', 0, 'attachment', 'image/jpeg', 0",
        "95, 1, '2022-05-25 00:47:03', '2022-05-25 00:47:03', 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.', 'test Premium Quality 3', 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.', 'publish', 'open', 'closed', '', 'premium-quality-3-24', '', '', '2022-05-25 00:47:03', '2022-05-25 00:47:03', '', 0, 'https://www.ezimport.net/product/premium-quality-3-24/', 0, 'product', '', 0"
    ]
}
```
#### Get last id
```
GET: {url}/wp-json/ezimport/v1/get_last_id/{table}
```
