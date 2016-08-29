#coding:utf-8

import MySQLdb
import csv
import commands



# MySQLからデータをCSVで書き出す

con = MySQLdb.connect(
	host="",
	db="",
	user="",
	passwd=""
)

cur = con.cursor()

# gs13371tg, Ryohei, toyokichi_, yoyohashi927
# kksgck_4, mi_mimi0130, okuzaki1022, i_my_me15, m_rena818, yuui0201'
sql = "select Level, Time, Type, Latitude, Longitude, Steps from Eat"
# sql = "select Level, Time, Type, Latitude, Longitude, Steps from Eat where UserName = 'gs13371tg' or UserName ='Ryohei' or UserName ='toyokichi_' or UserName ='yoyohashi927'"
# sql = "select Level, Time, Type, Latitude, Longitude, Steps from Eat where UserName = 'kksgck_4' or UserName ='mi_mimi0130' or UserName ='okuzaki1022' or UserName ='i_my_me15' or UserName='m_rena818' or UserName='yuui0201'"
# sql = "select level, UserName from Eat where UserName='yoyohashi927'"

cur.execute(sql)

rows = cur.fetchall()

test = []

# for i in range(len(rows)-1):
# 	if rows[i][1] != rows[i+1][1]:
# 		apa = rows[i][2].split(":")
# 		apa = int(apa[0]) * 60 * 60 + int(apa[1]) * 60 + int(apa[2])
# 		ngo = rows[i+1][2].split(":")
# 		ngo = int(ngo[0]) * 60 * 60 + int(ngo[1]) * 60 + int(ngo[2])
# 		res = [(60*60*24 - apa + ngo)/3600, rows[i+1][0]]
# 		test.append(res)
# 	else:
# 		apa = rows[i][2].split(":")
# 		apa = int(apa[0]) * 60 * 60 + int(apa[1]) * 60 + int(apa[2])
# 		ngo = rows[i+1][2].split(":")
# 		ngo = int(ngo[0]) * 60 * 60 + int(ngo[1]) * 60 + int(ngo[2])
# 		res = [(ngo-apa)/3600, rows[i+1][0]]
# 		test.append(res)

# for i in range(len(rows)-1):
# 	if rows[i][1] != rows[i+1][1]:
# 		res = [rows[i+1][6], rows[i+1][0]]
# 		test.append(res)
# 	else:
# 		res = [int(rows[i+1][6]) - int(rows[i][6]), rows[i+1][0]]
# 		test.append(res)

# ave = 0.0
# for i in range(len(rows)):
# 	ave += int(rows[i][0])

# ave = ave/int(len(rows))
# res = [rows[1][1], ave]
# test.append(res)



cur.close()
con.close()

file = "test.csv"

f = open(file, "w")

writer = csv.writer(f, lineterminator='\n')
writer.writerows(rows)

f.close()


