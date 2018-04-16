## Files

* `SuperSQL.php` - Main file
* `SuperSQL_min.php`
* `SuperSQL_helper.php` - Helper functions
* `SuperSQL_helper_min.php`

### Sizes

* `SuperSQL.php` - 28558 Chars (28.6 MB)
* `SuperSQL_min.php` - 12644 Chars (12.6 MB)
* `SuperSQL_helper.php` - 11234 Chars (11.2 MB)
* `SuperSQL_helper_min.php` - 5558 Chars (5.6 MB)
* `SuperSQL_complete_min.php` - 17922 Chars (17.9 MB)

## Hashes

```
* SuperSQL.php - e66095702fc3364ed5b60ab9c58284c2
* SuperSQL_min.php - 4137ec30163845fb4142ee63363d7ebe
* SuperSQL_helper.php - adaddf5e3cdf5f612a55b3f13f918bd1
* SuperSQL_helper_min.php - e4319fa820edaa9ce87b904569424295
* SuperSQL_complete.php - 13985e2acec0e184772666c5b02feafb
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.0218ms Average Time, Sum: 0.6541ms

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.0013 | 0.0388 |
| 100 R Insert W Temp     | 0.0162 | 0.4846 |
| Select *                | 0.0007 | 0.0206 |
| Select * W Cast         | 0.0006 | 0.0169 |
| Select * W Cast W where | 0.0007 | 0.0197 |
| 1 Row Update            | 0.0013 | 0.0401 |
| Delete                  | 0.001 | 0.0311 |
