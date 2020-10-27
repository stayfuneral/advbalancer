window. onload = function () {
    new Vue({
        el: '#plugin-advbalancer-app',
        data: {
            title: 'Автобалансировщик заявок',
            limit: null,
            fetchUrl: '../configs/fetch.php'
        },
        methods: {
            async postData(url, data = {}) {

                const response = await fetch(url, {
                    method: 'POST',
                    body: JSON.stringify(data),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                return await response.json();
            },
             async getUserTicketsLimit() {
                const request = this.postData(this.fetchUrl, {
                    type: 'get_limit'
                });
                request.then(response => {
                    this.limit = response.limit;
                })
            },
            setUserTicketsLimit() {

                const request = this.postData(this.fetchUrl, {
                    type: 'set_limit',
                    limit: Number(this.limit)
                });

                request.then(response => {

                    if(response.result === 'success') {
                        alert('Лимит установлен успешно');
                        window.location.reload(true);
                    }

                })

            }
        },
        created() {
            this.getUserTicketsLimit();
        }
    });
}