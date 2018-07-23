## Files

* `SuperSQL.php` - Main file
* `SuperSQL_min.php`
* `SuperSQL_helper.php` - Helper functions
* `SuperSQL_helper_min.php`

### Sizes

* `SuperSQL.php` - 28819 Chars (28.8 MB)
* `SuperSQL_min.php` - 12751 Chars (12.8 MB)
* `SuperSQL_helper.php` - 11234 Chars (11.2 MB)
* `SuperSQL_helper_min.php` - 5558 Chars (5.6 MB)
* `SuperSQL_complete_min.php` - 18029 Chars (18 MB)

## Hashes

```
* SuperSQL.php - 2d1797d1e0185d1f4aecfb9868392a18
* SuperSQL_min.php - 2d840638b4a8d854c0cf2eb1b70982a3
* SuperSQL_helper.php - c28eb974df4b6b59cfd1703b93ba5ad5
* SuperSQL_helper_min.php - 434f45d06501865c04386f32874637bf
* SuperSQL_complete.php - 38ca2da715288b837e153b0adac8ecde
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.0206ms Average Time, Sum: 0.6191ms

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.001 | 0.0312 |
| 100 R Insert W Temp     | 0.0157 | 0.4703 |
| Select *                | 0.0005 | 0.016 |
| Select * W Cast         | 0.0005 | 0.0164 |
| Select * W Cast W where | 0.0006 | 0.0176 |
| 1 Row Update            | 0.0012 | 0.0371 |
| Delete                  | 0.0009 | 0.0281 |
