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
* SuperSQL.php - 2d1797d1e0185d1f4aecfb9868392a18
* SuperSQL_min.php - 2d840638b4a8d854c0cf2eb1b70982a3
* SuperSQL_helper.php - a994557ff31f0bc500ccf474467cc44a
* SuperSQL_helper_min.php - cd229ac7df1aff5ae71843948a78dd45
* SuperSQL_complete.php - 3ec45950b173df277f91e11716e21839
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.021ms Average Time, Sum: 0.6296ms

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.001 | 0.0304 |
| 100 R Insert W Temp     | 0.0161 | 0.4818 |
| Select *                | 0.0005 | 0.0163 |
| Select * W Cast         | 0.0006 | 0.0168 |
| Select * W Cast W where | 0.0006 | 0.0176 |
| 1 Row Update            | 0.0012 | 0.0367 |
| Delete                  | 0.0009 | 0.0277 |
