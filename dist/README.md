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

## Hashes

```
* SuperSQL.php - 66942702c88380d32b4d892220ef71b3
* SuperSQL_min.php - a9dbd023d0581eff17226b952d60e6d3
* SuperSQL_helper.php - cc769938f1b134cfd0aebb5effd84815
* SuperSQL_helper_min.php - 7e20cebbb2b331ecef0a9d3870de47f3
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.0179ms Average Time, Sum: 0.5372ms

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.001 | 0.0285 |
| 100 R Insert W Temp     | 0.0133 | 0.4004 |
| Select *                | 0.0005 | 0.0148 |
| Select * W Cast         | 0.0005 | 0.0139 |
| Select * W Cast W where | 0.0005 | 0.0155 |
| 1 Row Update            | 0.0011 | 0.0344 |
| Delete                  | 0.0009 | 0.0275 |
