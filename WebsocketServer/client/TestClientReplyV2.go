package main

import (
	"bytes"
	"crypto/tls"
	"encoding/binary"
	//"encoding/json"
	"fmt"
	"io/ioutil"
	"net"
	"os"
	"strconv"
	//"strings"
	//"strconv"
	"sync"
	"time"
)

const (
	VERSION_FLAG = 0xfc00 //二进制: 1111110000000000
	DATALEN_FLAG = 0x03ff //二进制: 0000001111111111
	VERSION_ONE  = 1
)

var g_mutex sync.Mutex

func loginPackage() []byte {
	token := []byte("21a73750b4ae42ec972540ac68332d9847734451")
	buf := new(bytes.Buffer)
	nLen := uint16(1 + 2 + 1 + 16 + len(token) + 2)
	vl := uint16(2<<10 | nLen) //verison 2
	sn := []uint8{49, 50, 51, 52, 53, 54, 55, 56, 57, 0, 0, 0, 0, 0, 0, 0}
	//sn := []byte{1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16}
	binary.Write(buf, binary.LittleEndian, uint8(0x55))
	binary.Write(buf, binary.LittleEndian, vl)
	binary.Write(buf, binary.LittleEndian, uint8(0x01))
	binary.Write(buf, binary.LittleEndian, sn)
	binary.Write(buf, binary.LittleEndian, token)
	binary.Write(buf, binary.LittleEndian, uint16(0xaa))
	//fmt.Println("loginPackage, len: ", len(buf.Bytes()), buf.Bytes())
	return buf.Bytes()
}
func initData(frameindex uint8, n int, start uint64) []byte {
	buf := new(bytes.Buffer)
	//vl := uint16(1<<10 | 0x63) //修改协议后一定要相应地改长度啊
	vl := uint16(2<<10 | 0x65)
	t := uint64(time.Now().Unix()*1000) - 6100
	var data = []interface{}{
		uint8(0x55),
		vl,
		uint8(0x05),
		t,
		float64(113.266842 + 0.0001*float64(n)),
		float64(23.23564 + 0.0001*float64(n)),
		float32(88.33333),
		//[]uint8{65, 66, 67, 68, 69, 70, 71, 72, 73, 73, 74, 75, 76, 77, 79, 78},
		[]uint8{49, 50, 51, 52, 53, 54, 55, 56, 57, 0, 0, 0, 0, 0, 0, 0},
		uint8(0x1),
		uint8(0x0),
		uint16(200),
		float32(1.111111),
		float32(2.222222),
		float32(3.33333333),
		uint8(0),
		uint64(0x6666),
		start,
		uint8(frameindex),
		[]uint8{0, 1, 0, 2, 0, 3, 0, 4},
		uint8(0x06),
		uint32(0x37),
		uint16(10 + n),
		uint16(100), //flow_speed
		uint16(0xaa),
	}
	for _, v := range data {
		err := binary.Write(buf, binary.LittleEndian, v)
		if err != nil {
			fmt.Println("binary.Write failed:", err)
		}
	}
	return buf.Bytes()
}

type GprsData struct {
	Sof           uint8
	VerLen        uint16
	CmdId         uint8
	NTimeStamp    uint64
	Longi         float64
	Lati          float64
	Alti          float32
	ProductId     [16]uint8
	SprayFlag     uint8
	MotorStatus   uint8
	RadarHeight   uint16
	VelocityX     float32
	VelocityY     float32
	FarmDeltaY    float32
	FarmMode      uint8
	Pilotnum      uint64
	Sessionnum    uint64
	FrameIndex    uint8
	FLightVersion [8]uint8
	Plant         uint8
	TeamID        uint32
	WorkArea      uint16
	FlowSpeed     uint16 //协议1.1新增字段
	CRC           uint16
}

var wg sync.WaitGroup

func main() {
	if len(os.Args) < 2 {
		fmt.Println("Usage: ", os.Args[0], "host:port")
		os.Exit(1)
	}
	var user, count int
	if len(os.Args) > 3 {
		user, _ = strconv.Atoi(os.Args[2])
		count, _ = strconv.Atoi(os.Args[3])
	} else {
		user = 1
		count = 1
	}

	sleepCnt := 100
	if len(os.Args) > 4 {
		sleepCnt, _ = strconv.Atoi(os.Args[4])
	}

	service := os.Args[1]
	i := 0
	fmt.Println("user: ", user)
	wg.Add(user)
	for i < user {
		go Sender(service, i, count, sleepCnt)
		i++
		time.Sleep(time.Millisecond * 1000)
	}
	wg.Wait()
	fmt.Println("sender user:", user, ", count:", count)
	os.Exit(0)
}
func SafeWrite(conn net.Conn, b []byte) error {
	g_mutex.Lock()
	defer g_mutex.Unlock()
	_, err := conn.Write(b)
	return err
}

type PackageHeadInfo struct {
	Head    uint8
	Ver     uint16
	DataLen uint16
	CmdID   uint8
}

func readHeadInfo(conn net.Conn) (info PackageHeadInfo, err error) {
	head := make([]byte, 1)
	version := make([]byte, 2)
	cmdid := make([]byte, 1)

	conn.Read(head)
	conn.Read(version)
	conn.Read(cmdid)

	info.Head = uint8(head[0])
	vl := binary.LittleEndian.Uint16(version)
	info.Ver = (vl & VERSION_FLAG) >> 10
	info.DataLen = (vl & DATALEN_FLAG)
	info.CmdID = uint8(cmdid[0])
	return
}
func returnLock(conn net.Conn, info PackageHeadInfo) {
	buf := new(bytes.Buffer)
	var err error
	bwrite := func(v interface{}) {
		if err == nil {
			err = binary.Write(buf, binary.LittleEndian, v)
		}
	}
	vl := uint16(VERSION_ONE<<10) | uint16(1+2+1+1+2)
	bwrite(info.Head)
	bwrite(vl)
	bwrite(uint8(4)) //cmd id
	bwrite(uint8(1)) // ack

	bwrite(uint16(8888)) //校验

	fmt.Println("reply: ", buf.Bytes())
	err = SafeWrite(conn, buf.Bytes())
	if err != nil {
		fmt.Println("send lock ack failed")
		fmt.Println(err)
	}
	//conn.Write(buf.Bytes())
}
func returnMsg(conn net.Conn, info PackageHeadInfo) {
	buf := new(bytes.Buffer)
	var err error
	bwrite := func(v interface{}) {
		if err == nil {
			err = binary.Write(buf, binary.LittleEndian, v)
		}
	}
	vl := uint16(VERSION_ONE<<10) | uint16(1+2+1+1+2)
	bwrite(info.Head)
	bwrite(vl)
	bwrite(uint8(7)) //cmd id
	bwrite(uint8(0)) // ack

	bwrite(uint16(8888)) //校验

	fmt.Println("reply: ", buf.Bytes())
	err = SafeWrite(conn, buf.Bytes())
	if err != nil {
		fmt.Println("send lock ack failed")
		fmt.Println(err)
	}
	//conn.Write(buf.Bytes())
}
func returnLockTime(conn net.Conn, info PackageHeadInfo) {
	buf := new(bytes.Buffer)
	var err error
	bwrite := func(v interface{}) {
		if err == nil {
			err = binary.Write(buf, binary.LittleEndian, v)
		}
	}
	vl := uint16(VERSION_ONE<<10) | uint16(1+2+1+1+2)
	bwrite(info.Head)
	bwrite(vl)
	bwrite(uint8(9)) //cmd id
	bwrite(uint8(0)) // ack

	bwrite(uint16(8888)) //校验

	fmt.Println("reply: ", buf.Bytes())
	err = SafeWrite(conn, buf.Bytes())
	if err != nil {
		fmt.Println("send lock ack failed")
		fmt.Println(err)
	}
	//conn.Write(buf.Bytes())
}
func Reader(conn net.Conn) {
	defer conn.Close()

	sn := make([]byte, 16)
	ack := make([]byte, 1)
	CRC16 := make([]byte, 2)
	for {
		/*		_, err := conn.Read(ack[0:])
				if err != nil {
					fmt.Println("readError...")
					return
				}
				fmt.Println("reader: ", bytes.TrimRight(ack[:], "\x00"))*/
		info, _ := readHeadInfo(conn)
		//fmt.Println(info)
		switch info.CmdID {
		case 0x02:
			conn.Read(ack)
			conn.Read(ack)
			conn.Read(CRC16)
		case 0x03:
			fmt.Println("lock receive...")
			conn.Read(sn) //把头部以外的数据读取掉
			conn.Read(ack)
			conn.Read(CRC16)
			fmt.Println(sn, ack, CRC16)
			returnLock(conn, info)
		case 0x06:
			fmt.Println("message receive...")
			conn.Read(sn) //把头部以外的数据读取掉
			msg := make([]byte, info.DataLen-22)
			conn.Read(msg)
			conn.Read(CRC16)
			fmt.Println(msg)
			returnMsg(conn, info)
		case 0x08:
			fmt.Println("lock time period receive...")
			conn.Read(sn)
			conn.Read(ack)
			str := make([]byte, info.DataLen-23)
			conn.Read(str)
			conn.Read(CRC16)
			fmt.Println(str)
			returnLockTime(conn, info)

		default:
			//fmt.Println("default...")
		}
		//fmt.Println("read next...")
	}
}
func Sender(service string, i, count, sleepCnt int) {
	defer wg.Done()
	conf := &tls.Config{
		InsecureSkipVerify: true,
	}
	conn, err := tls.Dial("tcp", service, conf)
	//fmt.Printf("%#v", conn)
	if err != nil {
		fmt.Println(err, i)
		return
	}
	fmt.Println("connet...", i)
	var ack [16]byte
	lp := loginPackage()
	fmt.Println("loginPackage: ", lp)
	conn.Write(lp)

	conn.Read(ack[0:])
	fmt.Println("loginPackage ack: ", ack, err)

	go Reader(conn) //多个goruntine可以在同一个Conn中调用方法

	start := uint64(time.Now().Unix() * 1000)
	for n := 0; n < count-1; n++ {
		//var buf [512]byte
		w := initData(uint8(n+1)%128, n, start)

		var d GprsData
		newbuf := bytes.NewReader(w)
		err = binary.Read(newbuf, binary.LittleEndian, &d)
		_, err := conn.Write(w)
		if err != nil {
			fmt.Println("disconnet...")
			conn.Close()
			break
		}
		time.Sleep(time.Millisecond * time.Duration(sleepCnt))
	}
	//发送最后一帧数据
	w := initData(uint8(128+count), count, start)
	var d GprsData
	newbuf := bytes.NewReader(w)
	err = binary.Read(newbuf, binary.LittleEndian, &d)
	fmt.Println(d)
	fmt.Println(w)
	conn.Write(w)
}

func ReadFile(path string) ([]byte, error) {
	fi, err := os.Open(path)
	if err == nil {
		defer fi.Close()
		fc, err := ioutil.ReadAll(fi)
		return fc, err
	} else {
		return []byte(""), err
	}
}
func checkError(err error) {
	if err != nil {
		fmt.Println("Fatal error ", err.Error())
		os.Exit(1)
	}
}
