<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Belanja extends CI_Controller {

	// Load Model
	public function __construct()
	{
		parent::__construct();
		$this->load->model('produk_model');
		$this->load->model('kategori_model');
		$this->load->model('konfigurasi_model');
		$this->load->model('pelanggan_model');
		$this->load->model('header_transaksi_model');
		$this->load->model('transaksi_model');
		$this->load->model('ongkir_model');
		// Laoad string kode transaksi 
		$this->load->helper('string');
	}
	public function index()
	{
		// Halaman Belanja
		$keranjang = $this->cart->contents();
		$prodak 	= $this->produk_model->listing();

		$data = array(	'title'		=> 'Keranjang Belanja',
						'keranjang'	=> $keranjang,
						'prodak'	=> $prodak,
						'isi'		=> 'belanja/list'
		);

		$this->load->view('layout/wrapper', $data, FALSE);
	}	
	public function sukses()
	{
		// Halaman Belanja
		$keranjang = $this->cart->contents();

		$data = array(	'title'		=> 'Belanja Berhasil',
						'isi'		=> 'belanja/sukses'
		);

		$this->load->view('layout/wrapper', $data, FALSE);
	}	
	// Checkout
	public function checkout()
	{
		// Ambil data dari ongkir
		$ongkir = $this->ongkir_model->detail();
		
		// Check pelanggan sudah login atau belum ? jika belum maka nanti harus registrasi dan juga login 

		// Kondisi udah login
		if($this->session->userdata('email')) {

			$email = $this->session->userdata('email');
			$nama_pelanggan = $this->session->userdata('nama_pelanggan');
			$pelanggan = $this->pelanggan_model->sudah_login($email,$nama_pelanggan);
		
			
			$keranjang = $this->cart->contents();
				// Validasi input
			$valid = $this->form_validation;

			$valid->set_rules('nama_pelanggan', 'Nama lengkap', 'required', 
					array(	'required'		=> '%s harus diisi'));

			$valid->set_rules('telepon', 'Nomor Telepon', 'required', 
					array(	'required'		=> '%s harus diisi'));

			$valid->set_rules('email', 'Email', 'required|valid_email', 
					array(	'required'		=> '%s harus diisi',
							'valid_email' 	=> '%s tidak valid'
							));
			$valid->set_rules('alamat', 'Alamat', 'required', 
					array(	'required'		=> '%s harus diisi'));
		

			if($valid->run()===FALSE) {
			// End Validasi

			$data = array(	'title'		=> 'Data Pembelian',
							'keranjang'	=> $keranjang,
							'pelanggan'	=> $pelanggan,
							'ongkir'	=> $ongkir,
							'kode_otomatis' => $this->transaksi_model->kode_otomatis(),
							'isi'		=> 'belanja/checkout'
							);

		$this->load->view('layout/wrapper', $data, FALSE);
		// Masuk database
		}else{

		$i = $this->input;
		// Validasi untuk belum memilih produk
		if($this->cart->total()<1){
			echo  '<script type="text/javascript">alert("Anda belum memilih produk.. Silahkan klik BELI untuk menambahkan produk... ");</script>';
			redirect(base_url('produk'),'refresh');
		}
		// Validasi untuk belum memilih ongkir 
		if($i->post('id_ongkir')<1){
			echo  '<script type="text/javascript">alert("Anda belum memilih ongkos kirim.. Silahkan pilih ongkos kirim yang tertera..");</script>';
			redirect(base_url('belanja/checkout'),'refresh');
		}
		
		$data = array( 	'id_pelanggan'		=> $pelanggan->id_pelanggan,
						'nama_pelanggan'	=> $i->post('nama_pelanggan'),
						'email'				=> $i->post('email'),
				 		'telepon'			=> $i->post('telepon'),	
						'alamat'			=> $i->post('alamat'),
						'kode_transaksi'	=> $i->post('kode_transaksi'),
						'tanggal_transaksi'	=> $i->post('tanggal_transaksi'),
						'status_bayar'		=> 'Belum',
						'ongkir'			=> $i->post('id_ongkir'),	
						'jumlah_transaksi'	=> $this->cart->total()+$i->post('id_ongkir'),	
						'tanggal_post'		=> date('Y-m-d H:i:s')
					);
		// Proses masuk ke header_Transaksi
		$this->header_transaksi_model->tambah($data);
		// Proses masuk ke tabel transaksi 
			foreach ($keranjang as $keranjang) { 
				$sub_total = $keranjang['price'] * $keranjang['qty'];

					$data = array(	    'id_pelanggan'		=> $pelanggan->id_pelanggan,
										'kode_transaksi'	=> $i->post('kode_transaksi'),
										'id_produk'			=> $keranjang['id'],
										'harga'				=> $keranjang['price'],
										'jumlah'			=> $keranjang['qty'],
										'ongkir'			=> $i->post('id_ongkir'),	
										'total_harga'		=> $sub_total,
										'tanggal_transaksi' => $i->post('tanggal_transaksi'),

									);
					$this->transaksi_model->tambah($data);
			}
			// End proses transaksi 
			// Setelah masuk ke tabel transaksi maka kosongkan keranjang
			$this->cart->destroy();
			// End pengosongan keranjang 
			$this->session->set_flashdata('sukses', 'Check Out Berhasil');
			redirect(base_url('belanja/sukses'),'refresh');
		}
		// End masuk database
		}else{
			// Kalau belum maka harus registrasi
			$this->session->set_flashdata('sukses', 'Silahkan login terlebih dahulu'); 
			redirect(base_url('registrasi'),'refresh');
		}
}
	// Tambahkan ke keranjang belanja
	public function add()
	{
		// Ambil data dari form
		$id 			= $this->input->post('id');
		$qty 			= $this->input->post('qty');
		$price			= $this->input->post('price');
		$name			= $this->input->post('name');
		$redirect_page	= $this->input->post('redirect_page');

		// Memasukan ke keranjang belanja
		$data = array( 	'id'	=> $id,
						'qty'	=> $qty,
						'price'	=> $price,
						'name'	=> $name
								);
		$this->cart->insert($data);
		// Redirect page
		redirect($redirect_page,'refresh');
	} 
	// Update Cart
	public function update_cart($rowid)
	{
		$prodak = $this->produk_model->home();

		// Jika ada data rowid
		if($rowid){
			$data = array(	'rowid'		=>	$rowid,
							'prodak'	=>  $prodak,
							'qty'		=>	$this->input->post('qty')

			);
			$this->cart->update($data);
			$this->session->set_flashdata('sukses', 'Data Keranjang Telah Diupdate');
			redirect(base_url('belanja'),'refresh');

		}else{
			// Jika ga ada rowid
			redirect('belanja','refresh');
		}
	}
	// Hapus Keranjang Belanja 
	public function hapus($rowid='')
	{
		if($rowid){
			// Hapus Per item keranjang
		$this->cart->remove($rowid);
		$this->session->set_flashdata('sukses', 'Data Keranjang Belanja Telah Di Hapus');
		redirect(base_url('belanja'),'refresh');

		}else{
			// Hapus Semua
		$this->cart->destroy();
		$this->session->set_flashdata('sukses', 'Data Keranjang Belanja Telah Di Hapus');
		redirect(base_url('belanja'),'refresh');
		}
	
	}

}

/* End of file Belanja.php */
/* Location: ./application/controllers/Belanja.php */