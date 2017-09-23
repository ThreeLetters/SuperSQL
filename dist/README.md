## Files

* `SuperSQL.php` - Main file
* `SuperSQL_min.php`
* `SuperSQL_helper.php` - Helper functions
* `SuperSQL_helper_min.php`

### Sizes

* `SuperSQL.php` - 28193 Chars (28.2 MB)
* `SuperSQL_min.php` - 12463 Chars (12.5 MB)
* `SuperSQL_helper.php` - 11234 Chars (11.2 MB)
* `SuperSQL_helper_min.php` - 5558 Chars (5.6 MB)
* `SuperSQL_complete_min.php` - 17741 Chars (17.7 MB)

## Hashes

```
* SuperSQL.php - 66942702c88380d32b4d892220ef71b3
* SuperSQL_min.php - a9dbd023d0581eff17226b952d60e6d3
* SuperSQL_helper.php - cc769938f1b134cfd0aebb5effd84815
* SuperSQL_helper_min.php - 7e20cebbb2b331ecef0a9d3870de47f3
* SuperSQL_complete.php - 45f07fe4860f0246b4cf9e408f044170
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.0173ms Average Time, Sum: 0.5194ms

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.0009 | 0.0269 |
| 100 R Insert W Temp     | 0.0128 | 0.3852 |
| Select *                | 0.0005 | 0.0147 |
| Select * W Cast         | 0.0005 | 0.0142 |
| Select * W Cast W where | 0.0005 | 0.0155 |
| 1 Row Update            | 0.0011 | 0.0338 |
| Delete                  | 0.0009 | 0.0273 |
