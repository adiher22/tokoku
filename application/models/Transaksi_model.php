<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	// Listing all transaksi

	public function listing(){

		$this->db->select('*');
		$this->db->from('transaksi');
		$this->db->order_by('id_transaksi', 'desc');
		$query = $this->db->get();
		return $query->result();
	}
	// Listing all header 
	public function kode_transaksi($kode_transaksi){

		$this->db->select('transaksi.*,
							produk.nama_produk,
							produk.kode_produk');
		$this->db->from('transaksi');
		// Join dengan produk
		$this->db->join('produk', 'produk.id_produk = transaksi.id_produk', 'left');
		// End Join
		$this->db->where('kode_transaksi', $kode_transaksi);
		$this->db->order_by('id_transaksi', 'desc');
		$query = $this->db->get();
		return $query->result();
	}
	public function kode_otomatis(){
		$this->db->select('right(kode_transaksi,3) as kode', false);
		$this->db->order_by('kode_transaksi','desc');
		$this->db->limit(1);
		$query=$this->db->get('transaksi');
		if($query->num_rows()<>0){
			$data=$query->row();
			$kode=intval($data->kode)+1;
		}else{
			$kode=1;
		} 

		$kodemax=str_pad($kode,3,"0", STR_PAD_LEFT);
		$kodejadi='TRK'.$kodemax;

		return $kodejadi;
	}
	// Detail User
	public function detail($id_transaksi){

		$this->db->select('*');
		$this->db->from('transaksi');
		$this->db->where('id_transaksi', $id_transaksi);
		$this->db->order_by('id_transaksi', 'desc');
		$query = $this->db->get();
		return $query->row();
	}
		// Count table
	public function count()
	{   
    $query = $this->db->get('transaksi');
    if($query->num_rows()>0)
    {
      return $query->num_rows();
    }
    else
    {
      return 0;
    }
	}
	// Detail slug transaksi
	public function read($slug_transaksi){

		$this->db->select('*');
		$this->db->from('transaksi');
		$this->db->where('slug_transaksi', $slug_transaksi);
		$this->db->order_by('id_transaksi', 'desc');
		$query = $this->db->get();
		return $query->row();
	}
	// Login transaksi
	public function login($transaksi,$password){

		$this->db->select('*');
		$this->db->from('transaksi');
		$this->db->where(array('transaksiname' => $transaksiname,
								'password' => SHA1($password)));
		$this->db->order_by('id_transaksi', 'desc');
		$query = $this->db->get();
		return $query->row();
	}
	// Tambah
	public function tambah($data){
		$this->db->insert('transaksi', $data);
	}
	// Edit
	public function edit($data){
		$this->db->where('id_transaksi', $data['id_transaksi']);
		$this->db->update('transaksi', $data);
	}
	// Delete
	public function delete($data){
		$this->db->where('id_transaksi', $data['id_transaksi']);
		$this->db->delete('transaksi',$data);
	}
}

/* End of file Transaksi_model.php */
/* Location: ./application/models/Transaksi_model.php */