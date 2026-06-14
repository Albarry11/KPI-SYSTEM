// src/pages/Reports.jsx
import { useState, useEffect } from 'react'
import api from '../utils/api.js'
import styles from './dashboard.module.css'

export default function Reports() {
  const [reports, setReports] = useState([])
  const [loading, setLoading] = useState(true)
  const [generating, setGenerating] = useState(false)

  useEffect(() => {
    loadReports()
  }, [])

  async function loadReports() {
    setLoading(true)
    try {
      const res = await api.get('/reports')
      setReports(res.data.data || [])
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  async function handleGenerate() {
    if (!window.confirm('Buat laporan evaluasi untuk periode aktif?')) return
    setGenerating(true)
    try {
      const res = await api.post('/reports/generate', { period_id: 1 })
      alert(res.data.message || 'Laporan berhasil dibuat!')
      await loadReports()
    } catch (err) {
      const msg = err.response?.data?.message || 'Gagal membuat laporan. Pastikan evaluasi sudah di-generate dan di-approve terlebih dahulu.'
      alert(msg)
    } finally {
      setGenerating(false)
    }
  }

  async function handleDelete(id) {
    if (!window.confirm('Hapus laporan ini?')) return
    try {
      await api.delete(`/reports/${id}`)
      setReports(r => r.filter(rep => rep.id !== id))
    } catch { alert('Gagal menghapus laporan.') }
  }

  function formatDate(dateStr) {
    if (!dateStr) return '-'
    return new Date(dateStr).toLocaleDateString('id-ID', {
      day: 'numeric', month: 'long', year: 'numeric'
    })
  }

  if (loading) return (
    <div className={styles.loading}>
      <div className={styles.spinner}/>Memuat...
    </div>
  )

  return (
    <div className={styles.page}>
      <div className={styles.topbar}>
        <div>
          <h1 className={styles.pageTitle}>Laporan Manajer</h1>
          <p className={styles.pageDesc}>Ringkasan evaluasi bulanan</p>
        </div>
        <button className={styles.btnPrimary} onClick={handleGenerate} disabled={generating}>
          {generating ? '⏳ Membuat...' : '+ Buat Laporan'}
        </button>
      </div>

      <div className={styles.content}>
        {reports.length === 0 ? (
          <div style={{display:'flex',alignItems:'center',justifyContent:'center',height:'60%',flexDirection:'column',gap:'12px'}}>
            <div style={{fontSize:'40px'}}>📋</div>
            <div style={{color:'var(--text-2)',fontFamily:'var(--font-display)',fontWeight:700,fontSize:'16px'}}>Belum ada laporan</div>
            <div style={{color:'var(--text-3)',fontSize:'13px',textAlign:'center',maxWidth:'320px'}}>
              Klik tombol "+ Buat Laporan" di atas untuk menghasilkan laporan.<br/>
              Pastikan evaluasi sudah di-generate dan di-approve terlebih dahulu.
            </div>
          </div>
        ) : (
          <div style={{display:'flex', flexDirection:'column', gap:'16px', padding:'0 4px'}}>
            {reports.map(report => (
              <div key={report.id} style={{
                background:'var(--card)', border:'1px solid var(--border)',
                borderRadius:'12px', padding:'20px', position:'relative'
              }}>
                <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:'12px'}}>
                  <div>
                    <h3 style={{margin:0, fontSize:'15px', fontWeight:700, color:'var(--text-1)', fontFamily:'var(--font-display)'}}>
                      {report.title}
                    </h3>
                    <p style={{margin:'4px 0 0', fontSize:'12px', color:'var(--text-3)'}}>
                      {report.period?.name} — Diterbitkan {formatDate(report.published_at)}
                    </p>
                  </div>
                  <button onClick={() => handleDelete(report.id)} style={{
                    background:'none', border:'none', color:'var(--danger)', cursor:'pointer',
                    fontSize:'12px', fontWeight:600, padding:'4px 8px'
                  }}>🗑</button>
                </div>

                <p style={{fontSize:'13px', color:'var(--text-2)', margin:'0 0 16px', lineHeight:'1.5'}}>
                  {report.summary}
                </p>

                <div style={{display:'grid', gridTemplateColumns:'repeat(3, 1fr)', gap:'10px'}}>
                  <div style={{background:'var(--bg)', borderRadius:'8px', padding:'10px 12px', textAlign:'center'}}>
                    <p style={{margin:0, fontSize:'11px', color:'var(--text-3)', fontWeight:600}}>RATA-RATA SKOR</p>
                    <p style={{margin:'4px 0 0', fontSize:'20px', fontWeight:800, fontFamily:'var(--font-mono)',
                      color: report.avg_score >= 75 ? 'var(--accent)' : report.avg_score >= 50 ? 'var(--warn)' : 'var(--danger)'
                    }}>{report.avg_score}</p>
                  </div>
                  <div style={{background:'var(--bg)', borderRadius:'8px', padding:'10px 12px', textAlign:'center'}}>
                    <p style={{margin:0, fontSize:'11px', color:'var(--text-3)', fontWeight:600}}>TOTAL KARYAWAN</p>
                    <p style={{margin:'4px 0 0', fontSize:'20px', fontWeight:800, fontFamily:'var(--font-mono)', color:'var(--text-1)'}}>
                      {report.total_employees}
                    </p>
                  </div>
                  <div style={{background:'var(--bg)', borderRadius:'8px', padding:'10px 12px', textAlign:'center'}}>
                    <p style={{margin:0, fontSize:'11px', color:'var(--text-3)', fontWeight:600}}>TOP PERFORMER</p>
                    <p style={{margin:'4px 0 0', fontSize:'13px', fontWeight:700, color:'var(--accent)'}}>
                      {report.top_performer?.name || '-'}
                    </p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}