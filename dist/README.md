## Files

* `SuperSQL.php` - Main file
* `SuperSQL_min.php`
* `SuperSQL_helper.php` - Helper functions
* `SuperSQL_helper_min.php`

### Sizes

* `SuperSQL.php` - 28413 Chars (28.4 MB)
* `SuperSQL_min.php` - 12593 Chars (12.6 MB)
* `SuperSQL_helper.php` - 11234 Chars (11.2 MB)
* `SuperSQL_helper_min.php` - 5558 Chars (5.6 MB)
* `SuperSQL_complete_min.php` - 17871 Chars (17.9 MB)

## Hashes

```
* SuperSQL.php - 384e2b0983336f63f344920ecb9af69f
* SuperSQL_min.php - 364fe31e0f443b1d4dd0db066e4d1bce
* SuperSQL_helper.php - a51136f4d7f18f300d7aa580b671bb46
* SuperSQL_helper_min.php - 932d6c473e9ab4c87c17319ed2723192
* SuperSQL_complete.php - 749d3090a380be2552dd7818bc301148
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.0175ms Average Time, Sum: 0.5255ms

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.0009 | 0.0285 |
| 100 R Insert W Temp     | 0.0132 | 0.3957 |
| Select *                | 0.0005 | 0.0139 |
| Select * W Cast         | 0.0004 | 0.0132 |
| Select * W Cast W where | 0.0005 | 0.0144 |
| 1 Row Update            | 0.0011 | 0.0323 |
| Delete                  | 0.0009 | 0.0256 |
