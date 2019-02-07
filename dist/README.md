## Files

* `SuperSQL.php` - Main file
* `SuperSQL_min.php`
* `SuperSQL_helper.php` - Helper functions
* `SuperSQL_helper_min.php`

### Sizes

* `SuperSQL.php` - 29028 Chars (29 MB)
* `SuperSQL_min.php` - 12794 Chars (12.8 MB)
* `SuperSQL_helper.php` - 11183 Chars (11.2 MB)
* `SuperSQL_helper_min.php` - 5523 Chars (5.5 MB)
* `SuperSQL_complete_min.php` - 18037 Chars (18 MB)

## Hashes

```
* SuperSQL.php - f86620f924a9858ac3b6c90527fed56b
* SuperSQL_min.php - 1d2ccee3e6d5b16ba8a1236ca806db85
* SuperSQL_helper.php - 365025da27941c40e2b5a0aea949a362
* SuperSQL_helper_min.php - 6609707cf7b09cbc342e291cf9c1831c
* SuperSQL_complete.php - f422bafb18af3b55840834d8e51d3230
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.006sec Average Time, Sum: 0.1791sec

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.0007 | 0.0211 |
| 100 R Insert W Temp     | 0.0029 | 0.0878 |
| Select *                | 0.0004 | 0.0118 |
| Select * W Cast         | 0.0003 | 0.0096 |
| Select * W Cast W where | 0.0003 | 0.009 |
| 1 Row Update            | 0.0006 | 0.0175 |
| Delete                  | 0.0007 | 0.022 |
