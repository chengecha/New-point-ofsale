<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use emberlabs\Barcode\BarcodeBase;
require APPPATH.'/views/barcodes/BarcodeBase.php';
require APPPATH.'/views/barcodes/Code39.php';
require APPPATH.'/views/barcodes/Code128.php';
require APPPATH.'/views/barcodes/Ean13.php';
require APPPATH.'/views/barcodes/Ean8.php';

/**
 * Barcode library
 *
 * Library with utilities to manage barcodes
 */

class Barcode_lib
{
	private $CI;
	private $supported_barcodes = array('Code39' => 'Code 39', 'Code128' => 'Code 128', 'Ean8' => 'EAN 8', 'Ean13' => 'EAN 13');

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function get_list_barcodes()
	{
		return $this->supported_barcodes;
	}

	public function get_barcode_config()
	{
		$data['company'] = $this->CI->config->item('company');
		$data['barcode_content'] = $this->CI->config->item('barcode_content');
		$data['barcode_type'] = $this->CI->config->item('barcode_type');
		$data['barcode_font'] = $this->CI->config->item('barcode_font');
		$data['barcode_font_size'] = $this->CI->config->item('barcode_font_size');
		$data['barcode_height'] = $this->CI->config->item('barcode_height');
		$data['barcode_width'] = $this->CI->config->item('barcode_width');
		$data['barcode_first_row'] = $this->CI->config->item('barcode_first_row');
		$data['barcode_second_row'] = $this->CI->config->item('barcode_second_row');
		$data['barcode_third_row'] = $this->CI->config->item('barcode_third_row');
		$data['barcode_num_in_row'] = $this->CI->config->item('barcode_num_in_row');
		$data['barcode_page_width'] = $this->CI->config->item('barcode_page_width');
		$data['barcode_page_cellspacing'] = $this->CI->config->item('barcode_page_cellspacing');
		$data['barcode_generate_if_empty'] = $this->CI->config->item('barcode_generate_if_empty');
		$data['barcode_formats'] = $this->CI->config->item('barcode_formats');

		return $data;
	}

	public function validate_barcode($barcode)
	{
		$barcode_type = $this->CI->config->item('barcode_type');
		$barcode_instance = $this->get_barcode_instance($barcode_type);

		return $barcode_instance->validate($barcode);
	}

	public static function barcode_instance($item, $barcode_config)
	{
		$barcode_instance = Barcode_lib::get_barcode_instance($barcode_config['barcode_type']);
		$is_valid = empty($item['item_number']) && $barcode_config['barcode_generate_if_empty'] || $barcode_instance->validate($item['item_number']);

		// if barcode validation does not succeed,
		if(!$is_valid)
		{
			$barcode_instance = Barcode_lib::get_barcode_instance();
		}
		$seed = Barcode_lib::barcode_seed($item, $barcode_instance, $barcode_config);
		$barcode_instance->setData($seed);

		return $barcode_instance;
	}

	private static function get_barcode_instance($barcode_type='Code128')
	{
		switch($barcode_type)
		{
			case 'Code39':
				return new emberlabs\Barcode\Code39();
				break;

			case 'Code128':
			default:
				return new emberlabs\Barcode\Code128();
				break;

			case 'Ean8':
				return new emberlabs\Barcode\Ean8();
				break;

			case 'Ean13':
				return new emberlabs\Barcode\Ean13();
				break;
		}
	}

	private static function barcode_seed($item, $barcode_instance, $barcode_config)
	{
		$seed = $barcode_config['barcode_content'] !== "id" && !empty($item['item_number']) ? $item['item_number'] : $item['item_id'];

		if($barcode_config['barcode_content'] !== "id" && !empty($item['item_number']))
		{
			$seed = $item['item_number'];
		}
		else
		{
			if($barcode_config['barcode_generate_if_empty'])
			{
				// generate barcode with the correct instance
				$seed = $barcode_instance->generate($seed);
			}
			else
			{
				$seed = $item['item_id'];
			}
		}
		return $seed;
	}

	private function generate_barcode($item, $barcode_config)
	{
		try
		{
			$barcode_instance = Barcode_lib::barcode_instance($item, $barcode_config);
			$barcode_instance->setDimensions($barcode_config['barcode_width'], $barcode_config['barcode_height']);

			$barcode_instance->draw();

			return $barcode_instance->base64();
		}
		catch(Exception $e)
		{
			echo 'Caught exception: ', $e->getMessage(), "\n";
		}
	}

	public function generate_receipt_barcode($barcode_content)
	{
		try
		{
			// Code128 is the default and used in this case for the receipts
			$barcode = $this->get_barcode_instance();

			// set the receipt number to generate the barcode for
			$barcode->setData($barcode_content);

			// width: 300, height: 50
			$barcode->setDimensions(300, 50);

			// draw the image
			$barcode->draw();

			return $barcode->base64();
		}
		catch(Exception $e)
		{
			echo 'Caught exception: ', $e->getMessage(), "\n";
		}
	}

	public function generate_receipt_qrcode($barcode_content)
	{
		try
		{
			$qrCode = \BaconQrCode\Encoder\Encoder::encode(
				$barcode_content,
				\BaconQrCode\Common\ErrorCorrectionLevel::L()
			);
			$matrix = $qrCode->getMatrix();
			$moduleCount = $matrix->getWidth();

			$pixelSize = 6;
			$imgSize = $moduleCount * $pixelSize;

			$im = imagecreate($imgSize, $imgSize);
			$black = imagecolorallocate($im, 0, 0, 0);
			$white = imagecolorallocate($im, 255, 255, 255);
			imagefill($im, 0, 0, $white);

			for ($y = 0; $y < $moduleCount; $y++)
			{
				for ($x = 0; $x < $moduleCount; $x++)
				{
					if ($matrix->get($x, $y))
					{
						$px = $x * $pixelSize;
						$py = $y * $pixelSize;
						$px2 = ($x + 1) * $pixelSize - 1;
						$py2 = ($y + 1) * $pixelSize - 1;
						imagefilledrectangle($im, $px, $py, $px2, $py2, $black);
					}
				}
			}

			ob_start();
			imagepng($im);
			$data = ob_get_clean();
			imagedestroy($im);

			return base64_encode($data);
		}
		catch(Exception $e)
		{
			echo 'Caught exception: ', $e->getMessage(), "\n";
		}
	}

	public function generate_receipt_qrcode_data($data)
	{
		$qrData = array();

		$qrData['company'] = $this->CI->config->item('company');
		$qrData['address'] = $this->CI->config->item('address');
		$qrData['phone'] = $this->CI->config->item('phone');

		if (isset($data['sale_id']))
		{
			$qrData['type'] = 'sale';
			$qrData['id'] = $data['sale_id'];
		}
		elseif (isset($data['receiving_id']))
		{
			$qrData['type'] = 'receiving';
			$qrData['id'] = $data['receiving_id'];
		}

		if (isset($data['employee']))
		{
			$qrData['employee'] = $data['employee'];
		}

		if (isset($data['transaction_time']))
		{
			$qrData['date'] = $data['transaction_time'];
		}

		if (isset($data['customer']) && !empty($data['customer']))
		{
			$qrData['customer'] = $data['customer'];
		}

		if (isset($data['supplier']) && !empty($data['supplier']))
		{
			$qrData['supplier'] = $data['supplier'];
		}

		if (isset($data['invoice_number']) && !empty($data['invoice_number']))
		{
			$qrData['invoice_number'] = $data['invoice_number'];
		}

		if (isset($data['quote_number']) && !empty($data['quote_number']))
		{
			$qrData['quote_number'] = $data['quote_number'];
		}

		if (isset($data['total']))
		{
			$qrData['total'] = $data['total'];
		}

		if (isset($data['subtotal']))
		{
			$qrData['subtotal'] = $data['subtotal'];
		}

		if (isset($data['payments_total']))
		{
			$qrData['payments_total'] = $data['payments_total'];
		}

		if (isset($data['amount_change']))
		{
			$qrData['amount_change'] = $data['amount_change'];
		}

		if (isset($data['payment_type']))
		{
			$qrData['payment_type'] = $data['payment_type'];
		}

		if (isset($data['comments']) && !empty($data['comments']))
		{
			$qrData['comments'] = $data['comments'];
		}

		if (isset($data['cart']) && is_array($data['cart']))
		{
			$items = array();
			foreach ($data['cart'] as $line => $item)
			{
				if (isset($item['print_option']) && $item['print_option'] == PRINT_YES)
				{
					$itemData = array(
						'name' => $item['name']
					);

					if (isset($item['attribute_values']) && !empty($item['attribute_values']))
					{
						$itemData['variant'] = $item['attribute_values'];
					}

					if (isset($item['description']) && !empty($item['description']))
					{
						$itemData['description'] = $item['description'];
					}

					$itemData['quantity'] = $item['quantity'];
					$itemData['price'] = $item['price'];
					$itemData['total'] = $item['total'];

					if (isset($item['serialnumber']) && !empty($item['serialnumber']))
					{
						$itemData['serialnumber'] = $item['serialnumber'];
					}

					$items[] = $itemData;
				}
			}

			if (!empty($items))
			{
				$qrData['items'] = $items;
			}
		}

		if (isset($data['payments']) && is_array($data['payments']))
		{
			$payments = array();
			foreach ($data['payments'] as $payment)
			{
				$payments[] = array(
					'type' => $payment['payment_type'],
					'amount' => $payment['payment_amount']
				);
			}

			if (!empty($payments))
			{
				$qrData['payments'] = $payments;
			}
		}

		return $this->generate_receipt_qrcode(json_encode($qrData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}

	public function display_barcode($item, $barcode_config)
	{
		$display_table = "<table>";
		$display_table .= "<tr><td align='center'>" . $this->manage_display_layout($barcode_config['barcode_first_row'], $item, $barcode_config) . "</td></tr>";
		$barcode = $this->generate_barcode($item, $barcode_config);
		$display_table .= "<tr><td align='center'><img src='data:image/png;base64,$barcode' /></td></tr>";
		$display_table .= "<tr><td align='center'>" . $this->manage_display_layout($barcode_config['barcode_second_row'], $item, $barcode_config) . "</td></tr>";
		$display_table .= "<tr><td align='center'>" . $this->manage_display_layout($barcode_config['barcode_third_row'], $item, $barcode_config) . "</td></tr>";
		$display_table .= "</table>";

		return $display_table;
	}

	private function manage_display_layout($layout_type, $item, $barcode_config)
	{
		$result = '';

		if($layout_type == 'name')
		{
			$result = $this->CI->lang->line('items_name') . " " . $item['name'];
		}
		elseif($layout_type == 'category' && isset($item['category']))
		{
			$result = $this->CI->lang->line('items_category') . " " . $item['category'];
		}
		elseif($layout_type == 'cost_price' && isset($item['cost_price']))
		{
			$result = $this->CI->lang->line('items_cost_price') . " " . to_currency($item['cost_price']);
		}
		elseif($layout_type == 'unit_price' && isset($item['unit_price']))
		{
			$result = $this->CI->lang->line('items_unit_price') . " " . to_currency($item['unit_price']);
		}
		elseif($layout_type == 'company_name')
		{
			$result = $barcode_config['company'];
		}
		elseif($layout_type == 'item_code')
		{
			$result = $barcode_config['barcode_content'] !== "id" && isset($item['item_number']) ? $item['item_number'] : $item['item_id'];
		}

		return character_limiter($result, 40);
	}

	public function listfonts($folder)
	{
		$array = array();

		if(($handle = opendir($folder)) !== FALSE)
		{
			while(($file = readdir($handle)) !== FALSE)
			{
				if(substr($file, -4, 4) === '.ttf')
				{
					$array[$file] = $file;
				}
			}
		}

		closedir($handle);

		array_unshift($array, $this->CI->lang->line('config_none'));

		return $array;
	}

	public function get_font_name($font_file_name)
	{
		return substr($font_file_name, 0, -4);
	}
}

?>
