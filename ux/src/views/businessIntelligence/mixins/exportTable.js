
export default {
    data() {
        return {
            downloadLoading: false
        }
    },

    props: {},

    computed: {},

    watch: {},

    mounted() {},

    methods: {
        handleDownload(header, Tabledata, name) {
            this.downloadLoading = true
            let tHeader = []
            let filterVal = []
            for (var i = 0; i < header.length; i++) {
                tHeader.push(header[i].name)
                filterVal.push(header[i].field)
            }
            const data = this.formatJson(filterVal, Tabledata)
            import('@/vendor/Export2Excel').then(excel => {
                excel.export_json_to_excel({
                    header: tHeader,
                    data,
                    filename: name,
                    autoWidth: true
                })
                this.downloadLoading = false
            })
        },
        formatJson(filterVal, jsonData) {
            return jsonData.map(v => filterVal.map(j => {
                if (j === 'timestamp') {
                    return parseTime(v[j])
                } else {
                    return v[j]
                }
            }))
        }
    },

    deactivated: function () {}

}
