- ### 
```

SELECT anl_id,stamp,wr_group,group_ac,wr_num,wr_mpp_current,wr_mpp_voltage
FROM `db__pv_dcist_AX102`
WHERE stamp > DATE_SUB(NOW(), INTERVAL 1 YEAR) 
LIMIT 100;

```
