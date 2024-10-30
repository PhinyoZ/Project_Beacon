import struct

# Constants
ADTYPE_FLAGS = 0x01
ADTYPE_COMPLETE_16_BIT_SERVICE_UUID = 0x03
ADTYPE_SERVICE_DATA = 0x16
UUID16LE_FOR_LINECORP = b'\x6f\xfe'  # 'fe6f' in little endian

# Function to create advertisement structure
def ad_structure(ad_type, ad_data):
    length = len(ad_data) + 1
    return struct.pack('BB', length, ad_type) + ad_data

# Function to create the PDU for LINE Simple Beacon
def create_line_simple_beacon_advertising_pdu(hwid, device_message):
    # Validate hwid (must be 10 hex digits)
    if len(hwid) != 10 or not all(c in '0123456789abcdefABCDEF' for c in hwid):
        raise ValueError('HWID must be a 10-digit hexadecimal string')

    # Validate device message (must be 2 to 26 hex digits)
    if len(device_message) % 2 != 0 or not all(c in '0123456789abcdefABCDEF' for c in device_message):
        raise ValueError('Device message must be a valid hexadecimal string')

    # Create the frame data
    frame_type_data = b'\x02'
    hwid_data = bytes.fromhex(hwid)
    measured_tx_power_data = b'\x7F'
    device_message_data = bytes.fromhex(device_message)

    line_simple_beacon_frame = frame_type_data + hwid_data + measured_tx_power_data + device_message_data

    # Combine into the final PDU
    pdu = (
        ad_structure(ADTYPE_FLAGS, b'\x06') +
        ad_structure(ADTYPE_COMPLETE_16_BIT_SERVICE_UUID, UUID16LE_FOR_LINECORP) +
        ad_structure(ADTYPE_SERVICE_DATA, UUID16LE_FOR_LINECORP + line_simple_beacon_frame)
    )

    return pdu

# Example usage
hwid = "0176fdf975"  # 10-digit HWID in hex
device_message = "ff"  # Example device message in hex
pdu = create_line_simple_beacon_advertising_pdu(hwid, device_message)
print(pdu)
