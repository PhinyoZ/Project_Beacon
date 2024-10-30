from blelib import *
import ubluetooth
import struct
import time

# Constants for BLE advertisement
ADTYPE_FLAGS = 0x01
ADTYPE_COMPLETE_16_BIT_SERVICE_UUID = 0x03
ADTYPE_SERVICE_DATA = 0x16
UUID16LE_FOR_LINECORP = b'\x6f\xfe'  # Little endian format

# Initialize Bluetooth
bt = ubluetooth.BLE()


# Function to create the advertisement structure
def ad_structure(ad_type, ad_data):
    length = len(ad_data) + 1
    return struct.pack('BB', length, ad_type) + ad_data

# Function to create the PDU for LINE Simple Beacon
def create_line_simple_beacon_advertising_pdu(hwid, device_message):
    # Validate hwid (10-digit hex)
    if len(hwid) != 10 or not all(c in '0123456789abcdefABCDEF' for c in hwid):
        raise ValueError('HWID must be a 10-digit hexadecimal string')

    # Validate device message (2-26 digit hex)
    if len(device_message) % 2 != 0 or not all(c in '0123456789abcdefABCDEF' for c in device_message):
        raise ValueError('Device message must be a valid hexadecimal string')

    # Build the frame data
    frame_type_data = b'\x02'
    hwid_data = bytes.fromhex(hwid)
    measured_tx_power_data = b'\x7F'
    device_message_data = bytes.fromhex(device_message)

    # Combine all parts into a single frame
    line_simple_beacon_frame = frame_type_data + hwid_data + measured_tx_power_data + device_message_data

    # Combine all parts into the final PDU
    pdu = (
        ad_structure(ADTYPE_FLAGS, b'\x06') +
        ad_structure(ADTYPE_COMPLETE_16_BIT_SERVICE_UUID, UUID16LE_FOR_LINECORP) +
        ad_structure(ADTYPE_SERVICE_DATA, UUID16LE_FOR_LINECORP + line_simple_beacon_frame)
    )

    return pdu

# Function to start advertising via BLE
def start_advertising(hwid, device_message):
    pdu = create_line_simple_beacon_advertising_pdu(hwid, device_message)
    
    bt.active(True)
    bt.gap_advertise(100, pdu)
    print("Started advertising with PDU:", pdu)

# Infinity loop to run code
HWID = "0176fdf975"  # Replace with actual HWID from user input or configuration
DEVICE_MESSAGE = "a0a3b33145fe"  # Replace with actual device message from user input or configuration

while True:
    start_advertising(HWID, DEVICE_MESSAGE)
    time.sleep(5)  # Adjust the sleep time if necessary to control the frequency of advertising
