DROP TABLE IF EXISTS `trunkalarmoptions`;

CREATE TABLE `trunkalarmoptions` (
  `engine` varchar(40),
  `pbxname` varchar(40),
  `trunkemail` varchar(40),
  `trunkext` varchar(15),
  `trunknumber` varchar(15),
	PRIMARY KEY  (`engine`)  
);
