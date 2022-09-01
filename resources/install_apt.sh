PROGRESS_FILE=/tmp/dependancy_jeezviz_in_progress
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
sudo apt-get update
echo 50 > ${PROGRESS_FILE}
sudo apt-get install -y python3-pip python3-dev python3-pyudev libudev-dev python3-setuptools python3-serial python3-requests libffi-dev libssl-dev
sudo pip3 install pyezviz
echo 100 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm ${PROGRESS_FILE}
