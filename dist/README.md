## Files

* `SuperSQL.php` - Main file
* `SuperSQL_min.php`
* `SuperSQL_helper.php` - Helper functions
* `SuperSQL_helper_min.php`

### Sizes

* `SuperSQL.php` - 28819 Chars (28.8 MB)
* `SuperSQL_min.php` - 12751 Chars (12.8 MB)
* `SuperSQL_helper.php` - 11183 Chars (11.2 MB)
* `SuperSQL_helper_min.php` - 5523 Chars (5.5 MB)
* `SuperSQL_complete_min.php` - 17994 Chars (18 MB)

## Hashes

```
* SuperSQL.php - 9322fa11120332928cdcef907d0c9739
* SuperSQL_min.php - be4de07786d750be3ba34c7f1835365a
* SuperSQL_helper.php - 97b60f80a572d8bda6995a0d4bbbda11
* SuperSQL_helper_min.php - b9144d655d6394a82ed6c11314ae4f2f
* SuperSQL_complete.php - 63ce7c6146feaa3178791a26da67f8d6
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.0054sec Average Time, Sum: 0.1611sec

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.0005 | 0.0136 |
| 100 R Insert W Temp     | 0.0027 | 0.0818 |
| Select *                | 0.0004 | 0.011 |
| Select * W Cast         | 0.0003 | 0.0098 |
| Select * W Cast W where | 0.0003 | 0.0077 |
| 1 Row Update            | 0.0005 | 0.0162 |
| Delete                  | 0.0007 | 0.0208 |
